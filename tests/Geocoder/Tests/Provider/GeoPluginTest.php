<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeoPlugin;

class GeoPluginTest extends TestCase
{
    public function testgetName()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $this->assertEquals('geo_plugin', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoPlugin provider does not support street addresses, only IP addresses.
     */
    public function testGeocodeWithNull()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoPlugin provider does not support street addresses, only IP addresses.
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoPlugin provider does not support street addresses, only IP addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $results  = $provider->geocode('127.0.0.1');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCounty()->getName());
        $this->assertEquals('localhost', $result->getRegion()->getName());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $results  = $provider->geocode('::1');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCounty()->getName());
        $this->assertEquals('localhost', $result->getRegion()->getName());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://www.geoplugin.net/json.gp?ip=74.200.247.59".
     */
    public function testGeocodeWithRealIPv4GetsNullContent()
    {
        $provider = new GeoPlugin($this->getMockAdapterReturns(null));
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://www.geoplugin.net/json.gp?ip=74.200.247.59".
     */
    public function testGeocodeWithRealIPv4GetsEmptyContent()
    {
        $provider = new GeoPlugin($this->getMockAdapterReturns(''));
        $provider->geocode('74.200.247.59');
    }

    public function testGeocodeWithRealIPv4()
    {
        $provider = new GeoPlugin($this->getAdapter());
        $results  = $provider->geocode('66.147.244.214');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];

        $this->assertEquals(40.711101999999997, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(-73.946899000000002, $result->getLongitude(), '', 0.0001);
        $this->assertNull($result->getLocality());
        $this->assertEquals('New York', $result->getRegion()->getName());
        $this->assertEquals('NY', $result->getRegion()->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoPlugin provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $provider->reverse(1, 2);
    }
}
