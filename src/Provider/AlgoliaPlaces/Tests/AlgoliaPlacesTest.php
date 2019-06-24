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
use Geocoder\Query\GeocodeQuery;
use Geocoder\Provider\AlgoliaPlaces\AlgoliaPlaces;
use Http\Client\Curl\Client as HttplugClient;

/**
 * @author Sébastien Barré <sebastien@sheub.eu>
 */
class AlgoliaPlacesTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    /**
     * Get a real HTTP client. If a cache dir is set to a path it will use cached responses.
     *
     * @return HttpClient
     */
    protected function getHttpClient($apiKey = null, $appCode = null)
    {
        if (null !== $cacheDir = $this->getCacheDir()) {
            return new CachedResponseClient(new HttplugClient(), $cacheDir, $apiKey, $appCode);
        } else {
            return new HttplugClient();
        }
    }

    public function testGeocodeQueryWithLocale()
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
        $this->assertEquals(48.8653, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.39844, $result->getCoordinates()->getLongitude(), '', 0.01);

        $this->assertEquals('10 Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris 20e Arrondissement', $result->getLocality());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('fr', $result->getCountry()->getCode());
    }

    public function testGeocodeQueryWithoutLocale()
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
        $this->assertEquals(48.8546, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.34771, $result->getCoordinates()->getLongitude(), '', 0.01);

        $this->assertNull($result->getStreetName());
        $this->assertSame('75000', $result->getPostalCode());
        $this->assertSame('Paris', $result->getLocality());
        $this->assertSame('France', $result->getCountry()->getName());
        $this->assertSame('fr', $result->getCountry()->getCode());
    }

    public function testGeocodeUnauthenticated()
    {
        $provider = new AlgoliaPlaces($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('Paris, France'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(20, $results);
    }

    public function testGetName()
    {
        $provider = new AlgoliaPlaces($this->getMockedHttpClient(), 'appId', 'appCode');
        $this->assertEquals('algolia_places', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocodeWithInvalidData()
    {
        $provider = new AlgoliaPlaces($this->getMockedHttpClient(), 'appId', 'appCode');
        $provider->geocodeQuery(GeocodeQuery::create('foobar'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The AlgoliaPlaces provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeIpv4()
    {
        if (!isset($_SERVER['ALGOLIA_APP_ID']) || !isset($_SERVER['ALGOLIA_API_KEY'])) {
            $this->markTestSkipped('You need to configure the ALGOLIA_APP_ID and ALGOLIA_API_KEY value in phpunit.xml');
        }

        $provider = new AlgoliaPlaces($this->getHttpClient(), $_SERVER['ALGOLIA_API_KEY'], $_SERVER['ALGOLIA_APP_ID']);
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The AlgoliaPlaces provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new AlgoliaPlaces($this->getMockedHttpClient(), 'appId', 'appCode');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The AlgoliaPlaces provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        if (!isset($_SERVER['ALGOLIA_APP_ID']) || !isset($_SERVER['ALGOLIA_API_KEY'])) {
            $this->markTestSkipped('You need to configure the ALGOLIA_APP_ID and ALGOLIA_API_KEY value in phpunit.xml');
        }

        $provider = new AlgoliaPlaces($this->getHttpClient(), $_SERVER['ALGOLIA_API_KEY'], $_SERVER['ALGOLIA_APP_ID']);
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }
}
