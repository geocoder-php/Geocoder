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
use Geocoder\Provider\HostIp\HostIp;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class HostIpTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new HostIp($this->getMockedHttpClient());
        $this->assertEquals('host_ip', $provider->getName());
    }

    public function testGeocodeWithAddress(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Geocoder\Provider\HostIp\HostIp provider does not support Street addresses.');

        $provider = new HostIp($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $provider = new HostIp($this->getMockedHttpClient());
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

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The HostIp provider does not support IPv6 addresses.');

        $provider = new HostIp($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIPv4(): void
    {
        $provider = new HostIp($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('88.188.221.14'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(null, $result->getCoordinates());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Aulnat', $result->getLocality());
        $this->assertEmpty($result->getAdminLevels());
        $this->assertEquals('FRANCE', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
    }

    public function testGeocodeWithRealIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The HostIp provider does not support IPv6 addresses.');

        $provider = new HostIp($this->getHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }

    public function testReverse(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The HostIp provider is not able to do reverse geocoding.');

        $provider = new HostIp($this->getMockedHttpClient());
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    public function testGeocodeWithAnotherIp(): void
    {
        $provider = new HostIp($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('33.33.33.22'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNull($result->getCoordinates());
    }
}
