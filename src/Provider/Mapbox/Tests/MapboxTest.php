<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Mapbox\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Provider\Mapbox\Mapbox;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class MapboxTest extends BaseTestCase
{
    protected function getCacheDir(): ?string
    {
        if (isset($_SERVER['USE_CACHED_RESPONSES']) && true === $_SERVER['USE_CACHED_RESPONSES']) {
            return __DIR__.'/.cached_responses';
        }

        return null;
    }

    public function testGetName(): void
    {
        $provider = new Mapbox($this->getMockedHttpClient(), 'access_token');
        $this->assertEquals('mapbox', $provider->getName());
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Mapbox provider does not support IP addresses, only street addresses.');

        $provider = new Mapbox($this->getMockedHttpClient(), 'access_token');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Mapbox provider does not support IP addresses, only street addresses.');

        $provider = new Mapbox($this->getMockedHttpClient(), 'access_token');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIp(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Mapbox provider does not support IP addresses, only street addresses.');

        $provider = new Mapbox($this->getHttpClient(), 'access_token');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithQuotaExceeded(): void
    {
        $this->expectException(\Geocoder\Exception\QuotaExceeded::class);

        $provider = new Mapbox($this->getMockedHttpClient('', 429), 'access_token');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithInvalidApiKey(): void
    {
        // The v6 API answers 401 for an invalid token (v5 answered 403).
        // Both status codes map to InvalidCredentials in AbstractHttpProvider.
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);

        $provider = new Mapbox($this->getMockedHttpClient('', 401), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testReverseWithInvalidResponse(): void
    {
        $this->expectException(InvalidServerResponse::class);

        $provider = new Mapbox($this->getMockedHttpClient(), 'access_token');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }
}
