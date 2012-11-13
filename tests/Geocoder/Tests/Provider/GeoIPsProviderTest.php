<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeoIPsProvider;

class GeoIPsProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new GeoIPsProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('geo_ips', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new GeoIPsProvider($this->getMock('Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoIPsProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new GeoIPsProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoIPsProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new GeoIPsProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoIPsProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new GeoIPsProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new GeoIPsProvider($this->getMockAdapter($this->never()), 'api_key');
        $result   = $provider->getGeocodedData('127.0.0.1');

        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('zipcode', $result);
        $this->assertArrayNotHasKey('timezone', $result);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['county']);
        $this->assertEquals('localhost', $result['country']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoIPsProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new GeoIPsProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://api.geoips.com/ip/74.200.247.59/key/api_key/output/json/timezone/true/
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new GeoIPsProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://api.geoips.com/ip/74.200.247.59/key/api_key/output/json/timezone/true/
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new GeoIPsProvider($this->getMockAdapterReturns(''), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4GetsFakeContentFormattedEmpty()
    {
        $json = '{"response":{
            "status" : "Success",
            "ip" : "66.147.244.214",
            "hostname" : "box714.bluehost.com",
            "owner" : "",
            "continent_name" : "",
            "continent_code" : "",
            "country_name" : "",
            "country_code" : "",
            "region_name" : "",
            "region_code" : "",
            "county_name" : "",
            "city_name" : "",
            "latitude" : "",
            "longitude" : "",
            "timezone" : ""
        }}';

        $provider = new GeoIPsProvider($this->getMockAdapterReturns($json), 'api_key');
        $result   = $provider->getGeocodedData('66.147.244.214');

        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealIPv4GetsFakeContent()
    {
        $json = '{"response":{
            "status" : "Success",
            "ip" : "66.147.244.214",
            "hostname" : "box714.bluehost.com",
            "owner" : "BLUEHOST INC.",
            "continent_name" : "NORTH AMERICA",
            "continent_code" : "NA",
            "country_name" : "UNITED STATES",
            "country_code" : "US",
            "region_name" : "UTAH",
            "region_code" : "UT",
            "county_name" : "UTAH",
            "city_name" : "PROVO",
            "latitude" : "40.3402",
            "longitude" : "-111.6073",
            "timezone" : "MST"
        }}';

        $provider = new GeoIPsProvider($this->getMockAdapterReturns($json), 'api_key');
        $result   = $provider->getGeocodedData('66.147.244.214');

        $this->assertEquals('UNITED STATES', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertEquals('UTAH', $result['region']);
        $this->assertEquals('UT', $result['regionCode']);
        $this->assertEquals('UTAH', $result['county']);
        $this->assertEquals('PROVO', $result['city']);
        $this->assertEquals(40.3402, $result['latitude'], '', 0.0001);
        $this->assertEquals(-111.6073, $result['longitude'], '', 0.0001);
        $this->assertEquals('MST', $result['timezone']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['zipcode']);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedDataWithRealIPv4AndInvalidApiKeyGetsFakeContent()
    {
        $provider = new GeoIPsProvider($this->getMockAdapterReturns('{"response":{"status":"Forbidden", "message":"Not Authorized"}}'), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedDataWithRealIPv4AndInvalidApiKeyGetsFakeContent2()
    {
        $provider = new GeoIPsProvider($this->getMockAdapterReturns('{"response":{"status":"Forbidden", "message":"Account Inactive"}}'), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://api.geoips.com/ip/74.200.247.59/key/api_key/output/json/timezone/true/
     */
    public function testGetGeocodedDataGetsFakeContentWithIpNotFound()
    {
        $provider = new GeoIPsProvider($this->getMockAdapterReturns('{"response":{"status":"Bad Request", "message":"IP Not Found"}}'), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        if (!isset($_SERVER['GEOIPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the GEOIPS_API_KEY value in phpunit.xml');
        }

        $provider = new GeoIPsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['GEOIPS_API_KEY']);
        $result   = $provider->getGeocodedData('66.147.244.214');

        $this->assertEquals('UNITED STATES', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertEquals('UTAH', $result['region']);
        $this->assertEquals('UT', $result['regionCode']);
        $this->assertEquals('UTAH', $result['county']);
        $this->assertEquals('PROVO', $result['city']);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals(40.3402, $result['latitude'], '', 0.0001);
        $this->assertEquals(-111.6073, $result['longitude'], '', 0.0001);
        $this->assertEquals('MST', $result['timezone']);
        $this->assertNull($result['streetName']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoIPsProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new GeoIPsProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getReversedData(array(1, 2));
    }
}
