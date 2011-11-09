<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;

use Geocoder\Provider\HostIpProvider;

class HostIpProviderTest extends TestCase
{
    public function testGetGeocodedDataWithNull()
    {
        $this->provider = new HostIpProvider($this->getMockAdapter());
        $result = $this->provider->getGeocodedData(null);

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetGeocodedDataWithEmpty()
    {
        $this->provider = new HostIpProvider($this->getMockAdapter());
        $result = $this->provider->getGeocodedData('');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
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
        $this->assertNull($result['country']);
    }

    public function testGetGeocodedDataWithLocalhost()
    {
        $this->provider = new HostIpProvider($this->getMockAdapter($this->never()));
        $result = $this->provider->getGeocodedData('127.0.0.1');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['zipcode']);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['country']);
    }

    public function testGetGeocodedDataWithRealIp()
    {
        $this->provider = new HostIpProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter());
        $result = $this->provider->getGeocodedData('88.188.221.14');

        $this->assertEquals(45.5333, $result['latitude']);
        $this->assertEquals(2.6167, $result['longitude']);
        $this->assertEquals(null, $result['zipcode']);
        $this->assertEquals('Aulnat', $result['city']);
        $this->assertEquals(null, $result['region']);
        $this->assertEquals('FRANCE', $result['country']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetReverseData()
    {
        $this->provider = new HostIpProvider($this->getMockAdapter($this->never()));
        $this->provider->getReversedData(array(1, 2));
    }
}
