<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\MaxMindOmniProvider;

class MaxMindOmniProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new MaxMindOmniProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('maxmind_omni', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new MaxMindOmniProvider($this->getMock('Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MaxMindOmniProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new MaxMindOmniProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MaxMindOmniProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new MaxMindOmniProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MaxMindOmniProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new MaxMindOmniProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new MaxMindOmniProvider($this->getMockAdapter($this->never()), 'api_key');
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
     * @expectedExceptionMessage The MaxMindOmniProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new MaxMindOmniProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geoip.maxmind.com/e?l=api_key&i=74.200.247.59
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new MaxMindOmniProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geoip.maxmind.com/e?l=api_key&i=74.200.247.59
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new MaxMindOmniProvider($this->getMockAdapterReturns(''), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4GetsFakeContentFormattedEmpty()
    {
        $provider = new MaxMindOmniProvider($this->getMockAdapterReturns(',,,,,,,,,,,,,,,,,,,,,,,'), 'api_key');
        $result = $provider->getGeocodedData('74.200.247.59');

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
        $provider = new MaxMindOmniProvider($this->getMockAdapterReturns('US,United States,TX,Texas,Plano,33.034698486328,-96.813400268555,,,America/Chicago,,75093,,,,,,,,,,,,'), 'api_key');
        $result = $provider->getGeocodedData('74.200.247.59');

        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('TX', $result['regionCode']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals(33.034698486328, $result['latitude'], '', 0.0001);
        $this->assertEquals(-96.813400268555, $result['longitude'], '', 0.0001);
        $this->assertEquals('America/Chicago', $result['timezone']);
        $this->assertNull($result['streetName']);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedDataWithRealIPv4AndInvalidApiKeyGetsFakeContent()
    {
        $provider = new MaxMindOmniProvider($this->getMockAdapterReturns(',,,,,,,,,,,,,,,,,,,,,,,INVALID_LICENSE_KEY'), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedDataWithRealIPv4AndInvalidApiKeyGetsFakeContent2()
    {
        $provider = new MaxMindOmniProvider($this->getMockAdapterReturns(',,,,,,,,,,,,,,,,,,,,,,,LICENSE_REQUIRED'), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not retrieve informations for the ip address provided.
     */
    public function testGetGeocodedDataGetsFakeContentWithIpNotFound()
    {
        $provider = new MaxMindOmniProvider($this->getMockAdapterReturns(',,,,,,,,,,,,,,,,,,,,,,,IP_NOT_FOUND'), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMindOmniProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['MAXMIND_API_KEY']);
        $result = $provider->getGeocodedData('74.200.247.59');

        $this->assertEquals(33.034698486328, $result['latitude'], '', 0.0001);
        $this->assertEquals(-96.813400268555, $result['longitude'], '', 0.0001);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('TX', $result['regionCode']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertEquals('America/Chicago', $result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MaxMindOmniProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new MaxMindOmniProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getReversedData(array(1, 2));
    }
}