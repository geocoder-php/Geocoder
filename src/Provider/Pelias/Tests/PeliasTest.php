<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Pelias\Tests;

use Geocoder\Collection;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Provider\Pelias\Pelias;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class PeliasTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new Pelias($this->getMockedHttpClient(), 'http://localhost/');
        $this->assertEquals('pelias', $provider->getName());
    }

    public function testGeocode(): void
    {
        $provider = new Pelias($this->getMockedHttpClient('{}'), 'http://localhost/');
        $result = $provider->geocodeQuery(GeocodeQuery::create('foobar'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testReverse(): void
    {
        $provider = new Pelias($this->getMockedHttpClient('{}'), 'http://localhost/');
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The pelias provider does not support IP addresses, only street addresses.');

        $provider = new Pelias($this->getMockedHttpClient(), 'http://localhost/');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The pelias provider does not support IP addresses, only street addresses.');

        $provider = new Pelias($this->getMockedHttpClient(), 'http://localhost/');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIPv4(): void
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The pelias provider does not support IP addresses, only street addresses.');

        $provider = new Pelias($this->getMockedHttpClient(), 'http://localhost/');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithRealIPv6(): void
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The pelias provider does not support IP addresses, only street addresses.');

        $provider = new Pelias($this->getMockedHttpClient(), 'http://localhost/');
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));
    }
}
