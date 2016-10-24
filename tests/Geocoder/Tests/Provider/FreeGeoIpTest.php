<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Location;
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
     * @expectedExceptionMessage The FreeGeoIp provider does not support street addresses.
     */
    public function testGeocodeWithNull()
    {
        $provider = new FreeGeoIp($this->getMockAdapter($this->never()));
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The FreeGeoIp provider does not support street addresses.
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new FreeGeoIp($this->getMockAdapter($this->never()));
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The FreeGeoIp provider does not support street addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new FreeGeoIp($this->getMockAdapter($this->never()));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new FreeGeoIp($this->getMockAdapter($this->never()));
        $results  = $provider->geocode('127.0.0.1');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new FreeGeoIp($this->getMockAdapter($this->never()));
        $results  = $provider->geocode('::1');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
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
        $results  = $provider->geocode('74.200.247.59');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(33.0347, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-96.8134, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals(75093, $result->getPostalCode());
        $this->assertEquals('Plano', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Texas', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    public function testGeocodeWithRealIPv6()
    {
        $provider = new FreeGeoIp($this->getAdapter());
        $results  = $provider->geocode('::ffff:74.200.247.59');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(33.0347, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-96.8134, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals(75093, $result->getPostalCode());
        $this->assertEquals('Plano', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Texas', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
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
        $results  = $provider->geocode('74.200.247.59');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        $this->assertCount(1, $results->first()->getAdminLevels());
        $this->assertEquals('TX', $results->first()->getAdminLevels()->get(1)->getCode());
    }

    public function testGeocodeWithUSIPv6()
    {
        $provider = new FreeGeoIp($this->getAdapter());
        $results  = $provider->geocode('::ffff:74.200.247.59');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        $this->assertCount(1, $results->first()->getAdminLevels());
        $this->assertEquals('TX', $results->first()->getAdminLevels()->get(1)->getCode());
    }

    public function testGeocodeWithUKIPv4()
    {
        $provider = new FreeGeoIp($this->getAdapter());
        $results  = $provider->geocode('129.67.242.154');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);
        $this->assertEquals('GB', $results->first()->getCountry()->getCode());

        $this->assertCount(1, $results->first()->getAdminLevels());
        $this->assertEquals('ENG', $results->first()->getAdminLevels()->get(1)->getCode());
    }

    public function testGeocodeWithUKIPv6()
    {
        $provider = new FreeGeoIp($this->getAdapter());
        $results  = $provider->geocode('::ffff:129.67.242.154');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);
        $this->assertEquals('GB', $results->first()->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The FreeGeoIp provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new FreeGeoIp($this->getMockAdapter($this->never()));
        $provider->reverse(1, 2);
    }
}
