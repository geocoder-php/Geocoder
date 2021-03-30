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
use Geocoder\Exception\LogicException;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Provider\Pelias\Pelias;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use function http_build_query;
use function implode;
use function urlencode;
use function var_dump;

class PeliasTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new Pelias($this->getMockedHttpClient(), 'http://localhost/');
        self::assertEquals('pelias', $provider->getName());
    }

    public function testGeocode(): void
    {
        $client = $this->getMockedHttpClient('{}');
        $provider = new Pelias($client, 'http://localhost/');
        $result = $provider->geocodeQuery(GeocodeQuery::create('foobar'));

        self::assertEquals(0, $result->count());

        $query = $client->getRequests()[0]->getUri()->getQuery();
        self::assertStringContainsString('text=foobar', $query);
        self::assertStringNotContainsString('layers=', $query);
    }

    public function testGeocodeAcceptsLocales(): void
    {
        $client = $this->getMockedHttpClient('{}');
        $provider = new Pelias($client, 'http://localhost/');
        $result = $provider->geocodeQuery(GeocodeQuery::create('foobar')->withLocale('nl'));

        self::assertEquals(0, $result->count());

        $query = $client->getRequests()[0]->getUri()->getQuery();
        self::assertStringContainsString('lang=nl', $query);
    }

    public function testGeocodeLocaleDefaultsToEnglish(): void
    {
        $client = $this->getMockedHttpClient('{}');
        $provider = new Pelias($client, 'http://localhost/');
        $result = $provider->geocodeQuery(GeocodeQuery::create('foobar'));

        self::assertEquals(0, $result->count());

        $query = $client->getRequests()[0]->getUri()->getQuery();
        self::assertStringContainsString('lang=en', $query);
    }

    public function testGeoCodeWithLayers(): void
    {
        $client = $this->getMockedHttpClient('{}');
        $provider = new Pelias($client, 'http://localhost/');
        $provider->geocodeQuery(GeocodeQuery::create('foobar')
            ->withData('layers', [
                Pelias::LAYER_LOCALITY,
                Pelias::LAYER_REGION,
                Pelias::LAYER_COUNTRY,
            ])
        );

        $query = $client->getRequests()[0]->getUri()->getQuery();
        self::assertStringContainsString('layers='.urlencode('locality,region,country'), $query);
    }

    public function testGeocodeWithLayersNotBeingAnArray(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Layers must be an array');

        $provider = new Pelias($this->getMockedHttpClient(), 'http://localhost/');
        $provider->geocodeQuery(GeocodeQuery::create('foobar')
            ->withData('layers', 'not-an-array')
        );
    }

    public function testGeocodeWithInvalidLayer(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Invalid layers found. Valid layers are: ' . implode(', ', Pelias::VALID_LAYERS));

        $provider = new Pelias($this->getMockedHttpClient(), 'http://localhost/');
        $provider->geocodeQuery(GeocodeQuery::create('foobar')
            ->withData('layers', ['invalid-layer'])
        );
    }

    public function testReverse(): void
    {
        $provider = new Pelias($this->getMockedHttpClient('{}'), 'http://localhost/');
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));

        self::assertEquals(0, $result->count());
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
