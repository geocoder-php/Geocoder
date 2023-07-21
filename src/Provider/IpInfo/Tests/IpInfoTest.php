<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IpInfo\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Provider\IpInfo\IpInfo;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class IpInfoTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new IpInfo($this->getMockedHttpClient());
        $this->assertEquals('ip_info', $provider->getName());
    }

    public function testGeocodeWithRandomString(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The IpInfo provider does not support street addresses, only IP addresses.');

        $provider = new IpInfo($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('foobar'));
    }

    public function testGeocodeWithAddress(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The IpInfo provider does not support street addresses, only IP addresses.');

        $provider = new IpInfo($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    /** @dataProvider provideLocalhostIps */
    public function testGeocodeWithLocalhost(string $localhostIp): void
    {
        $provider = new IpInfo($this->getMockedHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create($localhostIp));

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
     * @return iterable<string[]>
     */
    public function provideLocalhostIps(): iterable
    {
        yield ['127.0.0.1'];
        yield ['::1'];
    }

    public function testGeocodeWithRealIPv4GetsNullContent(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

        $provider = new IpInfo($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));
    }

    public function testGeocodeWithRealIPv4(): void
    {
        $provider = new IpInfo($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(36.154, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-95.9928, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertEquals(74102, $result->getPostalCode());
        $this->assertEquals('Tulsa', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Oklahoma', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealIPv6(): void
    {
        $provider = new IpInfo($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('2601:9:7680:363:75df:f491:6f85:352f'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(39.934, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-74.891, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertEquals('08054', $result->getPostalCode());
        $this->assertEquals('Mount Laurel', $result->getLocality());
        $this->assertNull($result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testReverse(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The IpInfo provider is not able to do reverse geocoding.');

        $provider = new IpInfo($this->getMockedHttpClient());
        $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
    }
}
