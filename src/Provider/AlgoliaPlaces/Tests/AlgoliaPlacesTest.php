<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\AlgoliaPlaces\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\IntegrationTest\CachedResponseClient;
use Geocoder\Location;
use Geocoder\Provider\AlgoliaPlaces\AlgoliaPlaces;
use Geocoder\Query\GeocodeQuery;
use Http\Discovery\Psr18Client;
use Psr\Http\Client\ClientInterface;

/**
 * @author Sébastien Barré <sebastien@sheub.eu>
 */
class AlgoliaPlacesTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    /**
     * Get a real HTTP client. If a cache dir is set to a path it will use cached responses.
     */
    protected function getHttpClient(?string $apiKey = null, ?string $appCode = null): ClientInterface
    {
        return new CachedResponseClient(new Psr18Client(), $this->getCacheDir(), $apiKey, $appCode);
    }

    public function testGeocodeQueryWithLocale(): void
    {
        if (!isset($_SERVER['ALGOLIA_APP_ID']) || !isset($_SERVER['ALGOLIA_API_KEY'])) {
            $this->markTestSkipped('You need to configure the ALGOLIA_APP_ID and ALGOLIA_API_KEY value in phpunit.xml');
        }

        $provider = new AlgoliaPlaces($this->getHttpClient($_SERVER['ALGOLIA_API_KEY'], $_SERVER['ALGOLIA_APP_ID']), $_SERVER['ALGOLIA_API_KEY'], $_SERVER['ALGOLIA_APP_ID']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France')->withLocale('fr-FR'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.8653, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.39844, $result->getCoordinates()->getLongitude(), 0.01);

        $this->assertEquals('10 Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris 20e Arrondissement', $result->getLocality());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('fr', $result->getCountry()->getCode());
    }

    public function testGeocodeQueryWithoutLocale(): void
    {
        if (!isset($_SERVER['ALGOLIA_APP_ID']) || !isset($_SERVER['ALGOLIA_API_KEY'])) {
            $this->markTestSkipped('You need to configure the ALGOLIA_APP_ID and ALGOLIA_API_KEY value in phpunit.xml');
        }

        $provider = new AlgoliaPlaces($this->getHttpClient($_SERVER['ALGOLIA_API_KEY'], $_SERVER['ALGOLIA_APP_ID']), $_SERVER['ALGOLIA_API_KEY'], $_SERVER['ALGOLIA_APP_ID']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Paris, France'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(20, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.8546, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.34771, $result->getCoordinates()->getLongitude(), 0.01);

        $this->assertNull($result->getStreetName());
        $this->assertSame('75000', $result->getPostalCode());
        $this->assertSame('Paris', $result->getLocality());
        $this->assertSame('France', $result->getCountry()->getName());
        $this->assertSame('fr', $result->getCountry()->getCode());
    }

    public function testGeocodeUnauthenticated(): void
    {
        $provider = new AlgoliaPlaces($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('Paris, France'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(20, $results);
    }

    public function testGetName(): void
    {
        $provider = new AlgoliaPlaces($this->getMockedHttpClient(), 'appId', 'appCode');
        $this->assertEquals('algolia_places', $provider->getName());
    }

    public function testGeocodeWithInvalidData(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

        $provider = new AlgoliaPlaces($this->getMockedHttpClient(), 'appId', 'appCode');
        $provider->geocodeQuery(GeocodeQuery::create('foobar'));
    }

    public function testGeocodeIpv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The AlgoliaPlaces provider does not support IP addresses, only street addresses.');

        if (!isset($_SERVER['ALGOLIA_APP_ID']) || !isset($_SERVER['ALGOLIA_API_KEY'])) {
            $this->markTestSkipped('You need to configure the ALGOLIA_APP_ID and ALGOLIA_API_KEY value in phpunit.xml');
        }

        $provider = new AlgoliaPlaces($this->getHttpClient(), $_SERVER['ALGOLIA_API_KEY'], $_SERVER['ALGOLIA_APP_ID']);
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The AlgoliaPlaces provider does not support IP addresses, only street addresses.');

        $provider = new AlgoliaPlaces($this->getMockedHttpClient(), 'appId', 'appCode');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The AlgoliaPlaces provider does not support IP addresses, only street addresses.');

        if (!isset($_SERVER['ALGOLIA_APP_ID']) || !isset($_SERVER['ALGOLIA_API_KEY'])) {
            $this->markTestSkipped('You need to configure the ALGOLIA_APP_ID and ALGOLIA_API_KEY value in phpunit.xml');
        }

        $provider = new AlgoliaPlaces($this->getHttpClient(), $_SERVER['ALGOLIA_API_KEY'], $_SERVER['ALGOLIA_APP_ID']);
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }
}
