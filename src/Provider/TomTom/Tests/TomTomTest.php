<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\TomTom\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Provider\TomTom\TomTom;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class TomTomTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new TomTom($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('tomtom', $provider->getName());
    }

    public function testGeocodeWithAddress(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

        $provider = new TomTom($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('Tagensvej 47, 2200 København N'));
    }

    public function testGeocodeWithRealAddress(): void
    {
        $provider = new TomTom($this->getHttpClient($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Tagensvej 47, 2200 København N')->withLocale('en-GB'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(55.70, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(12.5529, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertNull($result->getBounds());
        $this->assertEquals(47, $result->getStreetNumber());
        $this->assertEquals('Tagensvej', $result->getStreetName());
        $this->assertEquals(2200, $result->getPostalCode());
        $this->assertEquals('Copenhagen', $result->getLocality());
        $this->assertCount(0, $result->getAdminLevels());
        $this->assertEquals('Denmark', $result->getCountry()->getName());
        $this->assertEquals('DK', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealAddressWithFrenchLocale(): void
    {
        $provider = new TomTom($this->getHttpClient($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Tagensvej 47, 2200 København N')->withLocale('fr-FR'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The TomTom provider does not support IP addresses, only street addresses.');

        $provider = new TomTom($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The TomTom provider does not support IP addresses, only street addresses.');

        $provider = new TomTom($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithIPv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The TomTom provider does not support IP addresses, only street addresses.');

        $provider = new TomTom($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The TomTom provider does not support IP addresses, only street addresses.');

        $provider = new TomTom($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));
    }

    public function testWithoutApiKey(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('No API key provided');

        new TomTom($this->getMockedHttpClient(), '');
    }

    public function testReverse(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

        $provider = new TomTom($this->getMockedHttpClient(), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    public function testReverseError400(): void
    {
        $error400 = <<<'JSON'
{
  "errorText": "Error parsing 'language': Language tag 'en-ES' not supported",
  "detailedError": {
    "code": "BadRequest",
    "message": "Error parsing 'language': Language tag 'en-ES' not supported",
    "target": "language"
  },
  "httpStatusCode": 400
}
JSON;

        $provider = new TomTom($this->getMockedHttpClient($error400), 'api_key');
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testReverseWithRealCoordinates(): void
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getHttpClient($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.86321648955345, 2.3887719959020615));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.86323, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(2.38877, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertNull($result->getBounds());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('75020', $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('20e Arrondissement Paris', $result->getSubLocality());
        $this->assertCount(0, $result->getAdminLevels());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealCoordinates(): void
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getHttpClient($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(56.5231, 10.0659));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(56.52435, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(10.06744, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertNull($result->getBounds());
        $this->assertEquals(16, $result->getStreetNumber());
        $this->assertEquals('Stabelsvej', $result->getStreetName());
        $this->assertEquals('8981', $result->getPostalCode());
        $this->assertEquals('Spentrup', $result->getLocality());
        $this->assertEquals('Spentrup', $result->getSubLocality());
        $this->assertCount(0, $result->getAdminLevels());
        $this->assertEquals('Denmark', $result->getCountry()->getName());
        $this->assertEquals('DK', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }
}
