<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;

use Geocoder\Provider\HostIpProvider;

class HostIpProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new HostIpProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $this->assertEquals('host_ip', $provider->getName());
    }
    
    public function testGetGeocodedDataWithNull()
    {
        $this->provider = new HostIpProvider($this->getMockAdapter());
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
        $this->provider = new HostIpProvider($this->getMockAdapter());
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
        $this->provider = new HostIpProvider($this->getMockAdapter());
        $result = $this->provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
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
        $this->provider = new HostIpProvider($this->getMockAdapter($this->never()));
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

    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $this->provider = new HostIpProvider($this->getMockAdapter($this->never()));
        $result = $this->provider->getGeocodedData('::1');

        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('zipcode', $result);
        $this->assertArrayNotHasKey('timezone', $result);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['county']);
        $this->assertEquals('localhost', $result['country']);
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        $this->provider = new HostIpProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $this->provider->getGeocodedData('88.188.221.14');

        $this->assertEquals(45.5333, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.6167, $result['longitude'], '', 0.0001);
        $this->assertArrayNotHasKey('zipcode', $result);
        $this->assertEquals('Aulnat', $result['city']);
        $this->assertArrayNotHasKey('region', $result);
        $this->assertEquals('FRANCE', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $this->provider = new HostIpProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $this->provider->getGeocodedData('::ffff:88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     */
    public function testGetReverseData()
    {
        $this->provider = new HostIpProvider($this->getMockAdapter($this->never()));
        $this->provider->getReversedData(array(1, 2));
    }

    public function testGetGeocodedDataWithAnotherIp()
    {
        $this->provider = new HostIpProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $this->provider->getGeocodedData('33.33.33.22');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
    }
}
