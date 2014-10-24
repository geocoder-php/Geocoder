<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeoIPs;

class GeoIPsTest extends TestCase
{
    public function testGetName()
    {
        $provider = new GeoIPs($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('geo_ips', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new GeoIPs($this->getMock('Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoIPs does not support street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new GeoIPs($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoIPs does not support street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new GeoIPs($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoIPs does not support street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new GeoIPs($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new GeoIPs($this->getMockAdapter($this->never()), 'api_key');
        $result   = $provider->getGeocodedData('127.0.0.1');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('postalCode', $result);
        $this->assertArrayNotHasKey('timezone', $result);

        $this->assertEquals('localhost', $result['locality']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['county']);
        $this->assertEquals('localhost', $result['country']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoIPs does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new GeoIPs($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Invalid response from GeoIPs server for query http://api.geoips.com/ip/74.200.247.59/key/api_key/output/json/timezone/true/
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new GeoIPs($this->getMockAdapterReturns(null), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Invalid response from GeoIPs server for query http://api.geoips.com/ip/74.200.247.59/key/api_key/output/json/timezone/true/
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new GeoIPs($this->getMockAdapterReturns(''), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4GetsFakeContentFormattedEmpty()
    {
        $json = '{"response":{
            "status": "Propper Request",
            "message": "Success",
            "notes": "The following results has been returned",
            "code": "200_1",
            "location": {
                "ip" : "66.147.244.214",
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
            },
            "unit_test": {
                "elapsed_time": "0.0676",
                "memory_usage": "2.2MB"
            }
        }}';

        $provider = new GeoIPs($this->getMockAdapterReturns($json), 'api_key');
        $result   = $provider->getGeocodedData('66.147.244.214');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['locality']);
        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['postalCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealIPv4GetsFakeContent()
    {
        $json = '{"response":{
            "status": "Propper Request",
            "message": "Success",
            "notes": "The following results has been returned",
            "code": "200_1",
            "location": {
                "ip" : "66.147.244.214",
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
            }
        }}';

        $provider = new GeoIPs($this->getMockAdapterReturns($json), 'api_key');
        $result   = $provider->getGeocodedData('66.147.244.214');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals('UNITED STATES', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertEquals('UTAH', $result['region']);
        $this->assertEquals('UT', $result['regionCode']);
        $this->assertEquals('UTAH', $result['county']);
        $this->assertEquals('PROVO', $result['locality']);
        $this->assertEquals(40.3402, $result['latitude'], '', 0.0001);
        $this->assertEquals(-111.6073, $result['longitude'], '', 0.0001);
        $this->assertEquals('MST', $result['timezone']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['postalCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage The API key associated with your request was not recognized.
     */
    public function testGetGeocodedDataWithRealIPv4AndInvalidApiKeyGetsFakeContent()
    {
        $provider = new GeoIPs(
            $this->getMockAdapterReturns(
                '{
                    "error": {
                        "status": "Forbidden",
                        "message": "Not Authorized",
                        "notes": "The API key associated with your request was not recognized",
                        "code": "403_1",
                        "unit_test": {
                            "elapsed_time": "0.0474",
                            "memory_usage": "2.2MB"
                        }
                    }
                }'
            ),
            'api_key'
        );
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage The API key has not been approved or has been disabled.
     */
    public function testGetGeocodedDataWithRealIPv4AndInvalidApiKeyGetsFakeContent2()
    {
        $provider = new GeoIPs(
            $this->getMockAdapterReturns(
                '{
                    "error": {
                        "status": "Forbidden",
                        "message": "Account Inactive",
                        "notes": "The API key has not been approved or has been disabled.",
                        "code": "403_2",
                        "unit_test": {
                            "elapsed_time": "0.0474",
                            "memory_usage": "2.2MB"
                        }
                    }
                }'
            ),
            'api_key'
        );
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceeded
     * @expectedExceptionMessage The service you have requested is over capacity.
     */
    public function testGetGeocodedDataWithRealIPv4AndQuotaExceeded()
    {
        $provider = new GeoIPs(
            $this->getMockAdapterReturns(
                '{
                    "error": {
                        "status": "Forbidden",
                        "message": "Limit Exceeded",
                        "notes": "The service you have requested is over capacity.",
                        "code": "403_3",
                        "unit_test": {
                            "elapsed_time": "0.0474",
                            "memory_usage": "2.2MB"
                        }
                    }
                }'
            ),
            'api_key'
        );
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgument
     * @expectedExceptionMessage The API call should include a valid IP address.
     */
    public function testGetGeocodedDataGetsFakeContentWithIpNotFound()
    {
        $provider = new GeoIPs(
            $this->getMockAdapterReturns(
                '{
                    "error": {
                        "status": "Bad Request",
                        "message": "Error in the URI",
                        "notes": "The API call should include a valid IP address.",
                        "code": "400_2",
                        "unit_test": {
                            "elapsed_time": "0.0474",
                            "memory_usage": "2.2MB"
                        }
                    }
                }'
            ),
            'api_key'
        );
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage The API call should include a API key parameter.
     */
    public function testGetGeocodedDataGetsFakeContentWithKeyNotFound()
    {
        $provider = new GeoIPs(
            $this->getMockAdapterReturns(
                '{
                    "error": {
                        "status": "Bad Request",
                        "message": "Error in the URI",
                        "notes": "The API call should include a API key parameter.",
                        "code": "400_1",
                        "unit_test": {
                            "elapsed_time": "0.0474",
                            "memory_usage": "2.2MB"
                        }
                    }
                }'
            ),
            'api_key'
        );
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        if (!isset($_SERVER['GEOIPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the GEOIPS_API_KEY value in phpunit.xml');
        }

        $provider = new GeoIPs($this->getAdapter(), $_SERVER['GEOIPS_API_KEY']);
        $result   = $provider->getGeocodedData('66.147.244.214');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals('UNITED STATES', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertEquals('UTAH', $result['region']);
        $this->assertEquals('UT', $result['regionCode']);
        $this->assertEquals('UTAH', $result['county']);
        $this->assertEquals('PROVO', $result['locality']);
        $this->assertNull($result['postalCode']);
        $this->assertEquals(40.3402, $result['latitude'], '', 0.0001);
        $this->assertEquals(-111.6073, $result['longitude'], '', 0.0001);
        $this->assertEquals('MST', $result['timezone']);
        $this->assertNull($result['streetName']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     */
    public function testGetGeocodedDataWithRealIPv4NoResults()
    {
        if (!isset($_SERVER['GEOIPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the GEOIPS_API_KEY value in phpunit.xml');
        }

        $provider = new GeoIPs($this->getAdapter(), $_SERVER['GEOIPS_API_KEY']);
        $result   = $provider->getGeocodedData('255.255.150.96');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoIPs is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new GeoIPs($this->getMockAdapter($this->never()), 'api_key');
        $provider->getReversedData(array(1, 2));
    }
}
