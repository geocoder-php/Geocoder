<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\FreeGeoIp;

class FreeGeoIpTest extends TestCase
{
    public function testGetName()
    {
        $provider = new FreeGeoIp($this->getMockAdapter($this->never()));
        $this->assertEquals('free_geo_ip', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The FreeGeoIp does not support Street addresses.
     */
    public function testGeocodeWithNull()
    {
        $provider = new FreeGeoIp($this->getMockAdapter($this->never()));
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The FreeGeoIp does not support Street addresses.
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new FreeGeoIp($this->getMockAdapter($this->never()));
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The FreeGeoIp does not support Street addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new FreeGeoIp($this->getMockAdapter($this->never()));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new FreeGeoIp($this->getMockAdapter($this->never()));
        $result   = $provider->geocode('127.0.0.1');

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

    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new FreeGeoIp($this->getMockAdapter($this->never()));
        $result   = $provider->geocode('::1');

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
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://freegeoip.net/json/74.200.247.59
     */
    public function testGeocodeWithRealIPv4GetsNullContent()
    {
        $provider = new FreeGeoIp($this->getMockAdapterReturns(null));
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://freegeoip.net/json/74.200.247.59
     */
    public function testGeocodeWithRealIPv4GetsEmptyContent()
    {
        $provider = new FreeGeoIp($this->getMockAdapterReturns(''));
        $provider->geocode('74.200.247.59');
    }

    public function testGeocodeWithRealIPv4()
    {
        $provider = new FreeGeoIp($this->getAdapter());
        $result   = $provider->geocode('74.200.247.59');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(33.0347, $result['latitude'], '', 0.01);
        $this->assertEquals(-96.8134, $result['longitude'], '', 0.01);
        $this->assertEquals(75093, $result['postalCode']);
        $this->assertEquals('Plano', $result['locality']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
    }

    public function testGeocodeWithRealIPv6()
    {
        $provider = new FreeGeoIp($this->getAdapter());
        $result   = $provider->geocode('::ffff:74.200.247.59');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(33.0347, $result['latitude'], '', 0.01);
        $this->assertEquals(-96.8134, $result['longitude'], '', 0.01);
        $this->assertEquals(75093, $result['postalCode']);
        $this->assertEquals('Plano', $result['locality']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://freegeoip.net/json/::ffff:74.200.247.59
     */
    public function testGeocodeWithRealIPv6GetsNullContent()
    {
        $provider = new FreeGeoIp($this->getMockAdapterReturns(null));
        $provider->geocode('::ffff:74.200.247.59');
    }

    public function testGeocodeWithUSIPv4()
    {
        $provider = new FreeGeoIp($this->getAdapter());
        $result   = $provider->geocode('74.200.247.59');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals('48', $result['regionCode']);
    }

    public function testGeocodeWithUSIPv6()
    {
        $provider = new FreeGeoIp($this->getAdapter());
        $result   = $provider->geocode('::ffff:74.200.247.59');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals('48', $result['regionCode']);
    }

    public function testGeocodeWithUKIPv4()
    {
        $provider = new FreeGeoIp($this->getAdapter());
        $result   = $provider->geocode('132.185.255.60');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals('H9', $result['regionCode']);
    }

    public function testGeocodeWithUKIPv6()
    {
        $provider = new FreeGeoIp($this->getAdapter());
        $result   = $provider->geocode('::ffff:132.185.255.60');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals('H9', $result['regionCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The FreeGeoIp is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new FreeGeoIp($this->getMockAdapter($this->never()));
        $provider->reverse(1, 2);
    }
}
