<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GoogleMaps\Tests;

use Geocoder\Exception\InvalidServerResponse;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Psr\Http\Message\RequestInterface;

class GoogleMapsTest extends BaseTestCase
{
    /**
     * @var string
     */
    private $testAPIKey = 'fake_key';

    protected function getCacheDir()
    {
        if (isset($_SERVER['USE_CACHED_RESPONSES']) && true === $_SERVER['USE_CACHED_RESPONSES']) {
            return __DIR__.'/.cached_responses';
        }

        return null;
    }

    public function testGetName()
    {
        $provider = new GoogleMaps($this->getMockedHttpClient());
        $this->assertEquals('google_maps', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new GoogleMaps($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMaps provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new GoogleMaps($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMaps provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIp()
    {
        $provider = new GoogleMaps($this->getHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceeded
     * @expectedExceptionMessage Daily quota exceeded https://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France
     */
    public function testGeocodeWithQuotaExceeded()
    {
        $provider = new GoogleMaps($this->getMockedHttpClient('{"status":"OVER_QUERY_LIMIT"}'));
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithRealAddress()
    {
        $provider = new GoogleMaps($this->getHttpClient(), 'Île-de-France');
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France')->withLocale('fr-FR'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.8630462, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.8630462, $result->getBounds()->getSouth(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getBounds()->getWest(), '', 0.001);
        $this->assertEquals(48.8630462, $result->getBounds()->getNorth(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getBounds()->getEast(), '', 0.001);
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Île-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealAddressWithSsl()
    {
        $provider = new GoogleMaps($this->getHttpClient(), null, null);
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.8630462, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.8630462, $result->getBounds()->getSouth(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getBounds()->getWest(), '', 0.001);
        $this->assertEquals(48.8630462, $result->getBounds()->getNorth(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getBounds()->getEast(), '', 0.001);
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Île-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeBoundsWithRealAddressForNonRooftopLocation()
    {
        $provider = new GoogleMaps($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('Paris, France'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.815573, $result->getBounds()->getSouth(), '', 0.0001);
        $this->assertEquals(2.224199, $result->getBounds()->getWest(), '', 0.0001);
        $this->assertEquals(48.902145, $result->getBounds()->getNorth(), '', 0.0001);
        $this->assertEquals(2.4699209, $result->getBounds()->getEast(), '', 0.0001);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testReverse()
    {
        $provider = new GoogleMaps($this->getMockedHttpClient());
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    public function testReverseWithRealCoordinates()
    {
        $provider = new GoogleMaps($this->getHttpClient());
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.8631507, 2.388911));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(12, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Île-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
    }

    public function testGeocodeWithCityDistrict()
    {
        $provider = new GoogleMaps($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Kalbach-Riedberg', $result->getSubLocality());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API key is invalid https://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France
     */
    public function testGeocodeWithInavlidApiKey()
    {
        $provider = new GoogleMaps($this->getMockedHttpClient('{"error_message":"The provided API key is invalid.", "status":"REQUEST_DENIED"}'));
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithRealValidApiKey()
    {
        if (!isset($_SERVER['GOOGLE_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the GOOGLE_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new GoogleMaps($this->getHttpClient($_SERVER['GOOGLE_GEOCODING_KEY']), null, $_SERVER['GOOGLE_GEOCODING_KEY']);

        $results = $provider->geocodeQuery(GeocodeQuery::create('Columbia University'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNotNull($result->getCoordinates()->getLatitude());
        $this->assertNotNull($result->getCoordinates()->getLongitude());
        $this->assertEquals('New York', $result->getLocality());
        $this->assertEquals('Manhattan', $result->getSubLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('New York', $result->getAdminLevels()->get(1)->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API key is invalid https://maps.googleapis.com/maps/api/geocode/json?address=Columbia%20University&key=fake_key
     */
    public function testGeocodeWithRealInvalidApiKey()
    {
        $provider = new GoogleMaps($this->getHttpClient(), null, $this->testAPIKey);

        $provider->geocodeQuery(GeocodeQuery::create('Columbia University'));
    }

    public function testGeocodePostalTown()
    {
        $provider = new GoogleMaps($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('CF37, United Kingdom'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Pontypridd', $result->getLocality());
    }

    public function testBusinessQueryWithoutPrivateKey()
    {
        $uri = '';

        $provider = GoogleMaps::business(
            $this->getMockedHttpClientCallback(
                function (RequestInterface $request) use (&$uri) {
                    $uri = $request->getUri();
                }
            ),
            'foo'
        );

        try {
            $provider->geocodeQuery(GeocodeQuery::create('blah'));
        } catch (InvalidServerResponse $e) {
        }
        $this->assertEquals('https://maps.googleapis.com/maps/api/geocode/json?address=blah&client=foo', $uri);
    }

    public function testBusinessQueryWithPrivateKey()
    {
        $uri = '';

        $provider = GoogleMaps::business(
            $this->getMockedHttpClientCallback(
                function (RequestInterface $request) use (&$uri) {
                    $uri = (string) $request->getUri();
                }
            ),
            'foo',
            'bogus'
        );

        try {
            $provider->geocodeQuery(GeocodeQuery::create('blah'));
        } catch (InvalidServerResponse $e) {
        }
        $this->assertEquals(
            'https://maps.googleapis.com/maps/api/geocode/json?address=blah&client=foo&signature=9G2weMhhd4E2ciR681gp9YabvUg=',
            $uri
        );
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     */
    public function testGeocodeWithInvalidClientIdAndKey()
    {
        $provider = GoogleMaps::business($this->getHttpClient(), 'foo', 'bogus');
        $provider->geocodeQuery(GeocodeQuery::create('Columbia University'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     */
    public function testGeocodeWithInvalidClientIdAndKeyNoSsl()
    {
        $provider = GoogleMaps::business($this->getHttpClient(), 'foo', 'bogus');
        $provider->geocodeQuery(GeocodeQuery::create('Columbia University'));
    }

    public function testGeocodeWithSupremise()
    {
        $provider = new GoogleMaps($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('2123 W Mineral Ave Apt 61,Littleton,CO8 0120'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('61', $result->getSubpremise());
    }
}
