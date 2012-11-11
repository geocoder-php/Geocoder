<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\MaxMindCityIspOrgProvider;

class MaxMindCityIspOrgProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new MaxMindCityIspOrgProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('maxmind_city', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new MaxMindCityIspOrgProvider($this->getMock('Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MaxMindCityIspOrgProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new MaxMindCityIspOrgProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MaxMindCityIspOrgProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new MaxMindCityIspOrgProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MaxMindCityIspOrgProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new MaxMindCityIspOrgProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new MaxMindCityIspOrgProvider($this->getMockAdapter($this->never()), 'api_key');
        $result = $provider->getGeocodedData('127.0.0.1');

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
     * @expectedExceptionMessage The MaxMindCityIspOrgProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new MaxMindCityIspOrgProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geoip.maxmind.com/f?l=api_key&i=74.200.247.59
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new MaxMindCityIspOrgProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geoip.maxmind.com/f?l=api_key&i=74.200.247.59
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new MaxMindCityIspOrgProvider($this->getMockAdapterReturns(''), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4GetsFakeContentFormattedEmpty()
    {
        $provider = new MaxMindCityIspOrgProvider($this->getMockAdapterReturns(',,,,,,,,,,'), 'api_key');
        $result = $provider->getGeocodedData('74.200.247.59');

        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['zipcode']);
    }

    public function testGetGeocodedDataWithRealIPv4GetsFakeContent()
    {
        $provider = new MaxMindCityIspOrgProvider($this->getMockAdapterReturns('US,TX,Plano,75093,33.034698486328,-96.813400268555,,,,'), 'api_key');
        $result = $provider->getGeocodedData('74.200.247.59');

        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertEquals('TX', $result['regionCode']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals(33.034698486328, $result['latitude'], '', 0.0001);
        $this->assertEquals(-96.813400268555, $result['longitude'], '', 0.0001);
        $this->assertNull($result['timezone']);

        $provider2 = new MaxMindCityIspOrgProvider($this->getMockAdapterReturns('FR,,,,,,,,,,'), 'api_key');
        $result2 = $provider2->getGeocodedData('74.200.247.59');
        $this->assertEquals('France', $result2['country']);

        $provider3 = new MaxMindCityIspOrgProvider($this->getMockAdapterReturns('GB,,,,,,,,,,'), 'api_key');
        $result3 = $provider3->getGeocodedData('74.200.247.59');
        $this->assertEquals('United Kingdom', $result3['country']);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedDataWithRealIPv4AndInvalidApiKeyGetsFakeContent()
    {
        $provider = new MaxMindCityIspOrgProvider($this->getMockAdapterReturns(',,,,,,,,,,INVALID_LICENSE_KEY'), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedDataWithRealIPv4AndInvalidApiKeyGetsFakeContent2()
    {
        $provider = new MaxMindCityIspOrgProvider($this->getMockAdapterReturns(',,,,,,,,,,LICENSE_REQUIRED'), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not retrieve informations for the ip address provided.
     */
    public function testGetGeocodedDataGetsFakeContentWithIpNotFound()
    {
        $provider = new MaxMindCityIspOrgProvider($this->getMockAdapterReturns(',,,,,,,,,,IP_NOT_FOUND'), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMindCityIspOrgProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['MAXMIND_API_KEY']);
        $result = $provider->getGeocodedData('74.200.247.59');

        $this->assertEquals(33.034698486328, $result['latitude'], '', 0.0001);
        $this->assertEquals(-96.813400268555, $result['longitude'], '', 0.0001);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('TX', $result['regionCode']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MaxMindCityIspOrgProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new MaxMindCityIspOrgProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getReversedData(array(1, 2));
    }
}