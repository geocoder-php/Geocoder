<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GeoPlugin\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Provider\GeoPlugin\GeoPlugin;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class GeoPluginTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testgetName(): void
    {
        $provider = new GeoPlugin($this->getMockedHttpClient());
        $this->assertEquals('geo_plugin', $provider->getName());
    }

    public function testGeocodeWithAddress(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The GeoPlugin provider does not support street addresses, only IP addresses.');

        $provider = new GeoPlugin($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $provider = new GeoPlugin($this->getMockedHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        $result = $results->first();
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $provider = new GeoPlugin($this->getMockedHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('::1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        $result = $results->first();
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    public function testGeocodeWithRealIPv4(): void
    {
        $provider = new GeoPlugin($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('66.147.244.214'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $this->assertEqualsWithDelta(40.711101999999997, $result->getCoordinates()->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(-73.946899000000002, $result->getCoordinates()->getLongitude(), 0.0001);
        $this->assertNull($result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('New York', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('NY', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    public function testReverse(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The GeoPlugin provider is not able to do reverse geocoding.');

        $provider = new GeoPlugin($this->getMockedHttpClient());
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }
}
