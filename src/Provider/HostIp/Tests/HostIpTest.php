<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\HostIp\Tests;

use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Tests\TestCase;
use Geocoder\Provider\HostIp\HostIp;

class HostIpTest extends TestCase
{
    public function testGetName()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $this->assertEquals('host_ip', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider does not support Street addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $results = $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNull($result->getCoordinates());

        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getTimezone());
        $this->assertEmpty($result->getAdminLevels());

        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider does not support IPv6 addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\ZeroResults
     */
    public function testGeocodeWithRealIPv4GetsNullContent()
    {
        $provider = new HostIp($this->getMockAdapterReturns(null));
        $provider->geocodeQuery(GeocodeQuery::create('88.188.221.14'));
    }

    /**
     * @expectedException \Geocoder\Exception\ZeroResults
     */
    public function testGeocodeWithRealIPv4GetsEmptyContent()
    {
        $provider = new HostIp($this->getMockAdapterReturns(''));
        $provider->geocodeQuery(GeocodeQuery::create('88.188.221.14'));
    }

    public function testGeocodeWithRealIPv4()
    {
        $provider = new HostIp($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('88.188.221.14'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(45.5333, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(2.6167, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Aulnat', $result->getLocality());
        $this->assertEmpty($result->getAdminLevels());
        $this->assertEquals('FRANCE', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider does not support IPv6 addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = new HostIp($this->getAdapter());
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    public function testGeocodeWithAnotherIp()
    {
        $provider = new HostIp($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('33.33.33.22'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNull($result->getCoordinates());
    }
}
