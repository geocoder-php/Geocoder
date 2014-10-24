<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeoPlugin;

class GeoPluginTest extends TestCase
{
    public function testGetName()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $this->assertEquals('geo_plugin', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoPlugin does not support street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoPlugin does not support street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoPlugin does not support street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $result   = $provider->getGeocodedData('127.0.0.1');

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

    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $result   = $provider->getGeocodedData('::1');

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
     * @expectedExceptionMessage Could not execute query http://www.geoplugin.net/json.gp?ip=74.200.247.59
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new GeoPlugin($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query http://www.geoplugin.net/json.gp?ip=74.200.247.59
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new GeoPlugin($this->getMockAdapterReturns(''));
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new GeoPlugin($this->getAdapter());
        $result   = $provider->getGeocodedData('66.147.244.214');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals('Provo', $result['locality']);
        $this->assertEquals(40.218102, $result['latitude'], '', 0.0001);
        $this->assertEquals(-111.613297, $result['longitude'], '', 0.0001);
        $this->assertEquals('UT', $result['regionCode']);
        $this->assertEquals('Utah', $result['region']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoPlugin is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $provider->getReversedData(array(1, 2));
    }
}
