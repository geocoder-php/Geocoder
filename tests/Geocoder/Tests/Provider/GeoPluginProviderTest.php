<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeoPluginProvider;

class GeoPluginProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new GeoPluginProviderTest($this->getMockAdapter($this->never()));
        $this->assertEquals('geo_plugin', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new GeoPluginProviderTest($this->getMock('Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoPluginProviderTest does not support street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new GeoPluginProviderTest($this->getMockAdapter($this->never()));
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoPluginProviderTest does not support street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new GeoPluginProviderTest($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoPluginProviderTest does not support street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new GeoPluginProviderTest($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new GeoPluginProviderTest($this->getMockAdapter($this->never()));
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

    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new GeoPluginProviderTest($this->getMockAdapter($this->never()));
        $result = $provider->getGeocodedData('::1');

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
        $provider = new GeoPluginProviderTest($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://www.geoplugin.net/json.gp?ip=74.200.247.59
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new GeoPluginProviderTest($this->getMockAdapterReturns(''));
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new GeoPluginProviderTest(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $provider->getGeocodedData('66.147.244.214');

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
     * @expectedExceptionMessage The GeoPluginProviderTest is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new GeoPluginProviderTest($this->getMockAdapter($this->never()));
        $provider->getReversedData(array(1, 2));
    }
}