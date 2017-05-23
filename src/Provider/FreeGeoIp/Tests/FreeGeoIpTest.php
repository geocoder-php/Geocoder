<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\FreeGeoIp\Tests;

use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Tests\TestCase;
use Geocoder\Provider\FreeGeoIp\FreeGeoIp;

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
        $provider->geocodeQuery(GeocodeQuery::create('xx'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The FreeGeoIp provider does not support street addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new FreeGeoIp($this->getMockAdapter($this->never()));
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new FreeGeoIp($this->getMockAdapter($this->never()));
        $results = $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));

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
        $results = $provider->geocodeQuery(GeocodeQuery::create('::1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocodeWithRealIPv4GetsNullContent()
    {
        $provider = new FreeGeoIp($this->getMockAdapterReturns(null));
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocodeWithRealIPv4GetsEmptyContent()
    {
        $provider = new FreeGeoIp($this->getMockAdapterReturns(''));
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithRealIPv4()
    {
        $provider = new FreeGeoIp($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));

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
        $results = $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));

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
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocodeWithRealIPv6GetsNullContent()
    {
        $provider = new FreeGeoIp($this->getMockAdapterReturns(null));
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));
    }

    public function testGeocodeWithUSIPv4()
    {
        $provider = new FreeGeoIp($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        $this->assertCount(1, $results->first()->getAdminLevels());
        $this->assertEquals('TX', $results->first()->getAdminLevels()->get(1)->getCode());
    }

    public function testGeocodeWithUSIPv6()
    {
        $provider = new FreeGeoIp($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        $this->assertCount(1, $results->first()->getAdminLevels());
        $this->assertEquals('TX', $results->first()->getAdminLevels()->get(1)->getCode());
    }

    public function testGeocodeWithUKIPv4()
    {
        $provider = new FreeGeoIp($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('129.67.242.154'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);
        $this->assertEquals('GB', $results->first()->getCountry()->getCode());

        $this->assertCount(1, $results->first()->getAdminLevels());
        $this->assertEquals('ENG', $results->first()->getAdminLevels()->get(1)->getCode());
    }

    public function testGeocodeWithUKIPv6()
    {
        $provider = new FreeGeoIp($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('::ffff:129.67.242.154'));

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
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }
}
