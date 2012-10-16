<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;

use Geocoder\Provider\IpInfoDbProvider;

class IpInfoDbProviderTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testGetDataWithNullApiKey()
    {
        $provider = new IpInfoDbProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    public function testGetGeocodedDataWithoutAdapter()
    {
        $this->provider = new IpInfoDbProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('foobar');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['county']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithNull()
    {
        $this->provider = new IpInfoDbProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData(null);

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['county']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithEmpty()
    {
        $this->provider = new IpInfoDbProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['county']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithAddress()
    {
        $this->provider = new IpInfoDbProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['county']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $this->provider = new IpInfoDbProvider($this->getMockAdapter($this->never()), 'api_key');
        $result = $this->provider->getGeocodedData('127.0.0.1');

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
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $this->provider = new IpInfoDbProvider($this->getMockAdapter($this->never()), 'api_key');
        $result = $this->provider->getGeocodedData('::1');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        if (!isset($_SERVER['IPINFODB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IPINFODB_API_KEY value in phpunit.xml');
        }

        $this->provider = new IpInfoDbProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['IPINFODB_API_KEY']);
        $result = $this->provider->getGeocodedData('74.125.45.100');

        $this->assertEquals(37.3861, $result['latitude'], '', 0.0001);
        $this->assertEquals(-122.084, $result['longitude'], '', 0.0001);
        $this->assertEquals(94043, $result['zipcode']);
        $this->assertEquals('MOUNTAIN VIEW', $result['city']);
        $this->assertEquals('CALIFORNIA', $result['region']);
        $this->assertEquals('UNITED STATES', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertEquals('America/Denver', $result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        if (!isset($_SERVER['IPINFODB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IPINFODB_API_KEY value in phpunit.xml');
        }

        $this->provider = new IpInfoDbProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['IPINFODB_API_KEY']);
        $result = $this->provider->getGeocodedData('::ffff:74.125.45.100');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     */
    public function testReversedData()
    {
        $this->provider = new IpInfoDbProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), 'api_key');
        $result = $this->provider->getReversedData(array());
    }
}
