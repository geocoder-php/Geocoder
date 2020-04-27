<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\HostIp\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Provider\HostIp\HostIpXml;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class HostIpXmlTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName()
    {
        $provider = new HostIpXml($this->getMockedHttpClient());
        $this->assertEquals('host_ip_xml', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geocoder\Provider\HostIp\HostIpXml provider does not support Street addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new HostIpXml($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new HostIpXml($this->getMockedHttpClient());
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
        $provider = new HostIpXml($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIPv4()
    {
        $provider = new HostIpXml($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('77.38.216.139'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(56.8833, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(24.0833, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Riga', $result->getLocality());
        $this->assertEmpty($result->getAdminLevels());
        $this->assertEquals('LATVIA', $result->getCountry()->getName());
        $this->assertEquals('LV', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider does not support IPv6 addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = new HostIpXml($this->getHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new HostIpXml($this->getMockedHttpClient());
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    public function testGeocodeWithAnotherIp()
    {
        $provider = new HostIpXml($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('33.33.33.22'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNull($result->getCoordinates());
    }
}
