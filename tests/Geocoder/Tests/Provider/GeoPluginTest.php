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
    public function testGetGeocodedDataWithNull()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoPlugin provider does not support street addresses, only IP addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoPlugin provider does not support street addresses, only IP addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $results  = $provider->geocode('127.0.0.1');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertEquals('Localhost', $result->getLocality());
        $this->assertEquals('Localhost', $result->getRegion()->getName());
        $this->assertEquals('Localhost', $result->getCounty()->getName());
        $this->assertEquals('Localhost', $result->getCountry()->getName());
    }

    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $results  = $provider->geocode('::1');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertEquals('Localhost', $result->getLocality());
        $this->assertEquals('Localhost', $result->getRegion()->getName());
        $this->assertEquals('Localhost', $result->getCounty()->getName());
        $this->assertEquals('Localhost', $result->getCountry()->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://www.geoplugin.net/json.gp?ip=74.200.247.59".
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new GeoPlugin($this->getMockAdapterReturns(null));
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://www.geoplugin.net/json.gp?ip=74.200.247.59".
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new GeoPlugin($this->getMockAdapterReturns(''));
        $provider->geocode('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new GeoPlugin($this->getAdapter());
        $results  = $provider->geocode('66.147.244.214');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertEquals('Provo', $result->getLocality());
        $this->assertEquals(40.218102, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(-111.613297, $result->getLongitude(), '', 0.0001);
        $this->assertEquals('UT', $result->getRegion()->getCode());
        $this->assertEquals('Utah', $result->getRegion()->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoPlugin provider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $provider->reverse(1, 2);
    }
}
