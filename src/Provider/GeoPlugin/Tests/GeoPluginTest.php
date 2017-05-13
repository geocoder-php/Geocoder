<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GeoPlugin\Tests;

use Geocoder\Model\Query\GeocodeQuery;
use Geocoder\Model\Query\ReverseQuery;
use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeoPlugin\GeoPlugin;

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
    public function testGeocodeWithAddress()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $results = $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        $result = $results->first();
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new GeoPlugin($this->getMockAdapter($this->never()));
        $results = $provider->geocodeQuery(GeocodeQuery::create('::1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        $result = $results->first();
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\ZeroResults
     */
    public function testGeocodeWithRealIPv4GetsNullContent()
    {
        $provider = new GeoPlugin($this->getMockAdapterReturns(null));
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\ZeroResults
     */
    public function testGeocodeWithRealIPv4GetsEmptyContent()
    {
        $provider = new GeoPlugin($this->getMockAdapterReturns(''));
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithRealIPv4()
    {
        $provider = new GeoPlugin($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('66.147.244.214'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $this->assertEquals(40.711101999999997, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(-73.946899000000002, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNull($result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('New York', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('NY', $result->getAdminLevels()->get(1)->getCode());
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
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }
}
