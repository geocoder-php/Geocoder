<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeoPluginProvider;

class GeoPluginProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new GeoPluginProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('geo_plugin', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoPluginProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new GeoPluginProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoPluginProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new GeoPluginProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoPluginProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new GeoPluginProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new GeoPluginProvider($this->getMockAdapter($this->never()));
        $result   = $provider->getGeocodedData('127.0.0.1');

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
        $provider = new GeoPluginProvider($this->getMockAdapter($this->never()));
        $result   = $provider->getGeocodedData('::1');

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
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://www.geoplugin.net/json.gp?ip=74.200.247.59
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new GeoPluginProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://www.geoplugin.net/json.gp?ip=74.200.247.59
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new GeoPluginProvider($this->getMockAdapterReturns(''));
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new GeoPluginProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('66.147.244.214');

        $this->assertEquals('Provo', $result['city']);
        $this->assertEquals(40.218102, $result['latitude'], '', 0.0001);
        $this->assertEquals(-111.613297, $result['longitude'], '', 0.0001);
        $this->assertEquals('UT', $result['regionCode']);
        $this->assertEquals('Utah', $result['region']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoPluginProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new GeoPluginProvider($this->getMockAdapter($this->never()));
        $provider->getReversedData(array(1, 2));
    }
}
