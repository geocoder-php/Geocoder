<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;

use Geocoder\Provider\FreeGeoIpProvider;

class FreeGeoIpProviderTest extends TestCase
{
    public function testGetGeocodedDataWithNull()
    {
        $this->provider = new FreeGeoIpProvider($this->getMockAdapter());
        $result = $this->provider->getGeocodedData(null);

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithEmpty()
    {
        $this->provider = new FreeGeoIpProvider($this->getMockAdapter());
        $result = $this->provider->getGeocodedData('');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithAddress()
    {
        $this->provider = new FreeGeoIpProvider($this->getMockAdapter());
        $result = $this->provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $this->provider = new FreeGeoIpProvider($this->getMockAdapter($this->never()));
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
        $this->provider = new FreeGeoIpProvider($this->getMockAdapter($this->never()));
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
        $this->provider = new FreeGeoIpProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $this->provider->getGeocodedData('74.200.247.59');

        $this->assertEquals(33.0347, $result['latitude'], '', 0.0001);
        $this->assertEquals(-96.8134, $result['longitude'], '', 0.0001);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
    }

    public function testGetGeocodedDataWithRealIPv6()
    {
        $this->provider = new FreeGeoIpProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $this->provider->getGeocodedData('::ffff:74.200.247.59');

        $this->assertEquals(33.0347, $result['latitude'], '', 0.0001);
        $this->assertEquals(-96.8134, $result['longitude'], '', 0.0001);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
    }

    public function testGetGeocodedDataWithUSIPv4()
    {
        $this->provider = new FreeGeoIpProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter());
        $result = $this->provider->getGeocodedData('74.200.247.59');

        $this->assertEquals('48', $result['regionCode']);
    }

    public function testGetGeocodedDataWithUSIPv6()
    {
        $this->provider = new FreeGeoIpProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter());
        $result = $this->provider->getGeocodedData('::ffff:74.200.247.59');

        $this->assertEquals('48', $result['regionCode']);
    }

    public function testGetGeocodedDataWithUKIPv4()
    {
        $this->provider = new FreeGeoIpProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter());
        $result = $this->provider->getGeocodedData('132.185.255.60');

        $this->assertEquals('H9', $result['regionCode']);
    }

    public function testGetGeocodedDataWithUKIPv6()
    {
        $this->provider = new FreeGeoIpProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter());
        $result = $this->provider->getGeocodedData('::ffff:132.185.255.60');

        $this->assertEquals('H9', $result['regionCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     */
    public function testGetReverseData()
    {
        $this->provider = new FreeGeoIpProvider($this->getMockAdapter($this->never()));
        $this->provider->getReversedData(array(1, 2));
    }
}
