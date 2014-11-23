<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\MaxMind;

class MaxMindTest extends TestCase
{
    public function testGetName()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('maxmind', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), null);
        $provider->geocode('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MaxMind provider does not support street addresses, only IP addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MaxMind provider does not support street addresses, only IP addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MaxMind provider does not support street addresses, only IP addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), 'api_key');
        $results  = $provider->geocode('127.0.0.1');

        $this->assertCount(1, $results);

        $result = $results[0]->toArray();
        $this->assertEquals('Localhost', $result['locality']);
        $this->assertEquals('Localhost', $result['region']);
        $this->assertEquals('Localhost', $result['county']);
        $this->assertEquals('Localhost', $result['country']);
    }

    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), 'api_key');
        $results  = $provider->geocode('::1');

        $this->assertCount(1, $results);

        $result = $results[0]->toArray();
        $this->assertEquals('Localhost', $result['locality']);
        $this->assertEquals('Localhost', $result['region']);
        $this->assertEquals('Localhost', $result['county']);
        $this->assertEquals('Localhost', $result['country']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage Unknown MaxMind service foo
     */
    public function testGetGeocodedDataWithRealIPv4AndNotSupportedService()
    {
        $provider = new MaxMind($this->getMockAdapter(), 'api_key', 'foo');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage Unknown MaxMind service 12345
     */
    public function testGetGeocodedDataWithRealIPv6AndNotSupportedService()
    {
        $provider = new MaxMind($this->getMockAdapter(), 'api_key', 12345);
        $provider->geocode('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://geoip.maxmind.com/f?l=api_key&i=74.200.247.59".
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(null), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://geoip.maxmind.com/f?l=api_key&i=74.200.247.59".
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(''), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4GetsFakeContentFormattedEmpty()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,'), 'api_key');
        $results  = $provider->geocode('74.200.247.59');

        $this->assertCount(1, $results);

        $result = $results[0]->toArray();
        $this->assertEmpty($result['country']);
        $this->assertEmpty($result['countryCode']);
        $this->assertEmpty($result['regionCode']);
        $this->assertEmpty($result['locality']);
        $this->assertEmpty($result['latitude']);
        $this->assertEmpty($result['longitude']);
        $this->assertEmpty($result['postalCode']);
        $this->assertEmpty($result['streetNumber']);
        $this->assertEmpty($result['streetName']);
        $this->assertEmpty($result['subLocality']);
        $this->assertEmpty($result['county']);
        $this->assertEmpty($result['countyCode']);
        $this->assertEmpty($result['region']);
        $this->assertEmpty($result['timezone']);
    }

    public function testGetGeocodedDataWithRealIPv4GetsFakeContent()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(
            'US,TX,Plano,75093,33.034698486328,-96.813400268555,,,,'), 'api_key');
        $results  = $provider->geocode('74.200.247.59');

        $this->assertCount(1, $results);

        $result = $results[0]->toArray();
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertEquals('TX', $result['regionCode']);
        $this->assertEquals('Plano', $result['locality']);
        $this->assertEquals(75093, $result['postalCode']);
        $this->assertEquals(33.034698486328, $result['latitude'], '', 0.0001);
        $this->assertEquals(-96.813400268555, $result['longitude'], '', 0.0001);
        $this->assertEmpty($result['timezone']);
        $this->assertEmpty($result['streetNumber']);
        $this->assertEmpty($result['streetName']);
        $this->assertEmpty($result['subLocality']);
        $this->assertEmpty($result['county']);
        $this->assertEmpty($result['countyCode']);
        $this->assertEmpty($result['region']);
        $this->assertEmpty($result['timezone']);

        $provider2 = new MaxMind($this->getMockAdapterReturns('FR,,,,,,,,,'), 'api_key');
        $result2   = $provider2->geocode('74.200.247.59');
        $this->assertEquals('France', $result2[0]->getCountry()->getName());

        $provider3 = new MaxMind($this->getMockAdapterReturns('GB,,,,,,,,,'), 'api_key');
        $result3   = $provider3->geocode('74.200.247.59');
        $this->assertEquals('United Kingdom', $result3[0]->getCountry()->getName());

        $provider4 = new MaxMind($this->getMockAdapterReturns(
            'US,CA,San Francisco,94110,37.748402,-122.415604,807,415,"Layered Technologies","Automattic"'), 'api_key');
        $results   = $provider4->geocode('74.200.247.59');

        $this->assertCount(1, $results);

        $result = $results[0]->toArray();
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertEquals('CA', $result['regionCode']);
        $this->assertEquals('San Francisco', $result['locality']);
        $this->assertEquals(94110, $result['postalCode']);
        $this->assertEquals(37.748402, $result['latitude'], '', 0.0001);
        $this->assertEquals(-122.415604, $result['longitude'], '', 0.0001);
        $this->assertEmpty($result['streetNumber']);
        $this->assertEmpty($result['streetName']);
        $this->assertEmpty($result['subLocality']);
        $this->assertEmpty($result['county']);
        $this->assertEmpty($result['countyCode']);
        $this->assertEmpty($result['region']);
        $this->assertEmpty($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedDataWithRealIPv4AndInvalidApiKeyGetsFakeContent()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,,INVALID_LICENSE_KEY'), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedOmniServiceDataWithRealIPv6AndInvalidApiKeyGetsFakeContent()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,,,,,,,,,,,,,,,,INVALID_LICENSE_KEY'),
            'api_key', MaxMind::OMNI_SERVICE);
        $provider->geocode('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedDataWithRealIPv4AndInvalidApiKey()
    {
        $provider = new MaxMind($this->getAdapter(), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedDataWithRealIPv4AndInvalidApiKeyGetsFakeContent2()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,,LICENSE_REQUIRED'), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedOmniServiceDataWithRealIPv6AndInvalidApiKeyGetsFakeContent2()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,,,,,,,,,,,,,,,INVALID_LICENSE_KEY'),
            'api_key', MaxMind::OMNI_SERVICE);
        $provider->geocode('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not retrieve information for the supplied IP address.
     */
    public function testGetGeocodedDataWithRealIPv4GetsFakeContentWithIpNotFound()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,,IP_NOT_FOUND'), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not retrieve information for the supplied IP address.
     */
    public function testGetGeocodedOmniServiceDataWithRealIPv6GetsFakeContentWithIpNotFound()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,,,,,,,,,,,,,,,IP_NOT_FOUND'),
            'api_key', MaxMind::OMNI_SERVICE);
        $provider->geocode('::fff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Invalid result returned by the API.
     */
    public function testGetGeocodedDataGetsFakeContentWithInvalidData()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,,'), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    public function testGetGeocodedServiceWithRealIPv4()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMind($this->getAdapter(), $_SERVER['MAXMIND_API_KEY']);
        $result   = $provider->geocode('74.200.247.159');

        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertEquals(33.034698, $result['latitude'], '', 0.1);
        $this->assertEquals(-96.813400, $result['longitude'], '', 0.1);
        $this->assertEquals('Plano', $result['locality']);
        $this->assertEquals(75093, $result['postalCode']);
        $this->assertEquals('TX', $result['regionCode']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['subLocality']);
        $this->assertNull($result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataOmniServiceWithRealIPv4()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMind($this->getAdapter(), $_SERVER['MAXMIND_API_KEY'],
            MaxMind::OMNI_SERVICE);
        $result   = $provider->geocode('74.200.247.159');

        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertEquals(33.0347, $result['latitude'], '', 0.1);
        $this->assertEquals(-96.8134, $result['longitude'], '', 0.1);
        $this->assertEquals('Plano', $result['locality']);
        $this->assertEquals(75093, $result['postalCode']);
        $this->assertEquals('TX', $result['regionCode']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['subLocality']);
        $this->assertNull($result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('America/Chicago', $result['timezone']);
    }

    public function testGetGeocodedDataWithRealIPv6()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMind($this->getAdapter(), $_SERVER['MAXMIND_API_KEY']);
        $result   = $provider->geocode('66.147.244.214');

        $this->assertCount(1, $result);

        $result = $result[0]->toArray();
        $this->assertEquals(40.2181, $result['latitude'], '', 0.1);
        $this->assertEquals(-111.6133, $result['longitude'], '', 0.1);
        $this->assertEquals('Provo', $result['locality']);
        $this->assertEquals(84606, $result['postalCode']);
        $this->assertEquals('UT', $result['regionCode']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['subLocality']);
        $this->assertNull($result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataOmniServiceWithRealIPv6WithSsl()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMind($this->getAdapter(), $_SERVER['MAXMIND_API_KEY'],
            MaxMind::OMNI_SERVICE, true);
        $result   = $provider->geocode('::ffff:66.147.244.214');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(40.2181, $result['latitude'], '', 0.1);
        $this->assertEquals(-111.6133, $result['longitude'], '', 0.1);
        $this->assertEquals('Provo', $result['locality']);
        $this->assertEquals(84606, $result['postalCode']);
        $this->assertEquals('UT', $result['regionCode']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['subLocality']);
        $this->assertNull($result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertEquals('Utah', $result['region']);
        $this->assertEquals('America/Denver', $result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MaxMind provider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), 'api_key');
        $provider->reverse(1, 2);
    }
}
