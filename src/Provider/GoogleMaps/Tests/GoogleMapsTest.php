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
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Provider\GoogleMaps\Model\GoogleAddress;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
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
        $provider = new GoogleMaps($this->getMockedHttpClient(), null, 'mock-api-key');
        $this->assertEquals('google_maps', $provider->getName());
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The GoogleMaps provider does not support IP addresses, only street addresses.');

        $provider = new GoogleMaps($this->getMockedHttpClient(), null, 'mock-api-key');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6()
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The GoogleMaps provider does not support IP addresses, only street addresses.');

        $provider = new GoogleMaps($this->getMockedHttpClient(), null, 'mock-api-key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIp()
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The GoogleMaps provider does not support IP addresses, only street addresses.');

        $provider = $this->getGoogleMapsProvider();
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithQuotaExceeded()
    {
        $this->expectException(\Geocoder\Exception\QuotaExceeded::class);
        $this->expectExceptionMessage('Daily quota exceeded https://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France');

        $provider = new GoogleMaps($this->getMockedHttpClient('{"status":"OVER_QUERY_LIMIT"}'), null, 'mock-api-key');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithRealAddress()
    {
        if (!isset($_SERVER['GOOGLE_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the GOOGLE_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new GoogleMaps($this->getHttpClient($_SERVER['GOOGLE_GEOCODING_KEY']), 'Ile-de-France', $_SERVER['GOOGLE_GEOCODING_KEY']);

        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France')->withLocale('fr-FR'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEqualsWithDelta(48.8630462, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(2.3882487, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(48.8617, $result->getBounds()->getSouth(), 0.001);
        $this->assertEqualsWithDelta(2.3882487, $result->getBounds()->getWest(), 0.001);
        $this->assertEqualsWithDelta(48.8644, $result->getBounds()->getNorth(), 0.001);
        $this->assertEqualsWithDelta(2.3901, $result->getBounds()->getEast(), 0.001);
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('Arrondissement de Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Île-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
        $this->assertEquals('ChIJ4b303vJt5kcRF9AQdh4ZjWc', $result->getId());
        $this->assertEquals(false, $result->isPartialMatch());

        // not provided
        $this->assertNull($result->getTimezone());
        $this->assertNull($result->getPostalCodeSuffix());
    }

    public function testGeocodeBoundsWithRealAddressForNonRooftopLocation()
    {
        $provider = $this->getGoogleMapsProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('Paris, France'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(48.815573, $result->getBounds()->getSouth(), 0.0001);
        $this->assertEqualsWithDelta(2.224199, $result->getBounds()->getWest(), 0.0001);
        $this->assertEqualsWithDelta(48.902145, $result->getBounds()->getNorth(), 0.0001);
        $this->assertEqualsWithDelta(2.4699209, $result->getBounds()->getEast(), 0.0001);
        $this->assertEquals('ChIJD7fiBh9u5kcRYJSMaMOCCwQ', $result->getId());
        $this->assertEquals(false, $result->isPartialMatch());
    }

    public function testReverse()
    {
        $this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

        $provider = new GoogleMaps($this->getMockedHttpClient(), null, 'mock-api-key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    public function testReverseWithRealCoordinates()
    {
        $provider = $this->getGoogleMapsProvider();
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.8631507, 2.388911));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals(12, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Arrondissement de Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Île-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
        $this->assertEquals('ChIJ9aLL3vJt5kcR61GCze3v6fg', $result->getId());
        $this->assertEquals(false, $result->isPartialMatch());
    }

    public function testReverseWithRealCoordinatesAndLocale()
    {
        $provider = $this->getGoogleMapsProvider();
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.8631507, 2.388911)->withLocale('fr-FR'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals(12, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Arrondissement de Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Île-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
        $this->assertEquals('ChIJ9aLL3vJt5kcR61GCze3v6fg', $result->getId());
        $this->assertEquals(false, $result->isPartialMatch());
    }

    public function testGeocodeWithCityDistrict()
    {
        $provider = $this->getGoogleMapsProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals('Kalbach-Riedberg', $result->getSubLocality());
        $this->assertEquals(false, $result->isPartialMatch());
    }

    public function testGeocodeWithInvalidApiKey()
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('API key is invalid https://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France');

        $provider = new GoogleMaps($this->getMockedHttpClient('{"error_message":"The provided API key is invalid.", "status":"REQUEST_DENIED"}'), null, 'mock-api-key');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithRealValidApiKey()
    {
        $provider = $this->getGoogleMapsProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('Columbia University'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertNotNull($result->getCoordinates()->getLatitude());
        $this->assertNotNull($result->getCoordinates()->getLongitude());
        $this->assertEquals('New York', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals(false, $result->isPartialMatch());
    }

    public function testGeocodeWithComponentFiltering()
    {
        $provider = $this->getGoogleMapsProvider();
        $query = GeocodeQuery::create('Sankt Petri')->withData('components', [
            'country' => 'SE',
            'locality' => 'Malmö',
        ]);

        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals('Malmö', $result->getLocality());
        $this->assertNotNull($result->getCountry());
        $this->assertEquals('SE', $result->getCountry()->getCode());
        $this->assertEquals(false, $result->isPartialMatch());
    }

    public function testCorrectlySerializesComponents()
    {
        $uri = '';

        $provider = new GoogleMaps(
            $this->getMockedHttpClientCallback(
                function (RequestInterface $request) use (&$uri) {
                    $uri = (string) $request->getUri();
                }
            ),
            null,
            'test-api-key'
        );

        $query = GeocodeQuery::create('address')->withData('components', [
            'country' => 'SE',
            'postal_code' => '22762',
            'locality' => 'Lund',
        ]);

        try {
            $provider->geocodeQuery($query);
        } catch (InvalidServerResponse $e) {
        }

        $this->assertEquals(
            'https://maps.googleapis.com/maps/api/geocode/json'.
            '?address=address'.
            '&components=country%3ASE%7Cpostal_code%3A22762%7Clocality%3ALund&key=test-api-key',
            $uri
        );
    }

    public function testCorrectlySetsComponents()
    {
        $uri = '';

        $provider = new GoogleMaps(
            $this->getMockedHttpClientCallback(
                function (RequestInterface $request) use (&$uri) {
                    $uri = (string) $request->getUri();
                }
            ),
            null,
            'test-api-key'
        );

        $query = GeocodeQuery::create('address')
            ->withData('components', 'country:SE|postal_code:22762|locality:Lund');

        try {
            $provider->geocodeQuery($query);
        } catch (InvalidServerResponse $e) {
        }

        $this->assertEquals(
            'https://maps.googleapis.com/maps/api/geocode/json'.
            '?address=address'.
            '&components=country%3ASE%7Cpostal_code%3A22762%7Clocality%3ALund&key=test-api-key',
            $uri
        );
    }

    public function testGeocodeWithRealInvalidApiKey()
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('API key is invalid https://maps.googleapis.com/maps/api/geocode/json?address=Columbia&key=fake_key');

        $provider = new GoogleMaps($this->getHttpClient($this->testAPIKey), null, $this->testAPIKey);
        $provider->geocodeQuery(GeocodeQuery::create('Columbia'));
    }

    public function testGeocodePostalTown()
    {
        $provider = $this->getGoogleMapsProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('CF37, United Kingdom'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals('Pontypridd', $result->getLocality());
        $this->assertEquals(false, $result->isPartialMatch());
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

    public function testBusinessQueryWithPrivateKeyAndChannel()
    {
        $uri = '';

        $provider = GoogleMaps::business(
            $this->getMockedHttpClientCallback(
                function (RequestInterface $request) use (&$uri) {
                    $uri = (string) $request->getUri();
                }
            ),
            'foo',
            'bogus',
            null,
            null,
            'bar'
        );

        try {
            $provider->geocodeQuery(GeocodeQuery::create('blah'));
        } catch (InvalidServerResponse $e) {
        }
        $this->assertEquals(
            'https://maps.googleapis.com/maps/api/geocode/json?address=blah&client=foo&channel=bar&signature=IdRm_EBPMWFgQNQ9eIDBxSWVlb8=',
            $uri
        );
    }

    public function testGeocodeWithInvalidClientIdAndKey()
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);

        $provider = GoogleMaps::business($this->getHttpClient(), 'foo', 'bogus');
        $provider->geocodeQuery(GeocodeQuery::create('Columbia University'));
    }

    public function testGeocodeWithInvalidClientIdAndKeyNoSsl()
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);

        $provider = GoogleMaps::business($this->getHttpClient(), 'foo', 'bogus');
        $provider->geocodeQuery(GeocodeQuery::create('Columbia University'));
    }

    public function testGeocodeWithSupremise()
    {
        $provider = $this->getGoogleMapsProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('2123 W Mineral Ave Apt 61,Littleton,CO8 0120'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals('61', $result->getSubpremise());
        $this->assertEquals(true, $result->isPartialMatch()); // 2123 W Mineral Ave #61, Littleton, CO 80120, USA
    }

    public function testGeocodeWithNaturalFeatureComponent()
    {
        $provider = $this->getGoogleMapsProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('Durmitor Nacionalni Park'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals('Durmitor', $result->getNaturalFeature());
        $this->assertEquals('Durmitor', $result->getPark());
        $this->assertEquals('Durmitor', $result->getPointOfInterest());
        $this->assertEquals('Montenegro', $result->getPolitical());
        $this->assertEquals('Montenegro', $result->getCountry());
        $this->assertEquals(false, $result->isPartialMatch());
    }

    public function testGeocodeWithAirportComponent()
    {
        $provider = $this->getGoogleMapsProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('Brisbane Airport'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals('Brisbane Airport', $result->getAirport());
        $this->assertEquals('Brisbane Airport', $result->getEstablishment());
        $this->assertEquals('Brisbane Airport', $result->getPointOfInterest());
        $this->assertEquals(false, $result->isPartialMatch());
    }

    public function testGeocodeWithPremiseComponent()
    {
        $provider = $this->getGoogleMapsProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('1125 17th St, Denver, CO 80202'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals('1125 17th Street', $result->getPremise());
        $this->assertEquals('Denver', $result->getLocality());
        $this->assertEquals('United States', $result->getCountry());
        $this->assertEquals('Central Business District', $result->getNeighborhood());
        $this->assertEquals(false, $result->isPartialMatch());
    }

    public function testGeocodeWithColloquialAreaComponent()
    {
        $provider = $this->getGoogleMapsProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('darwin'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals('Darwin', $result->getColloquialArea());
        $this->assertEquals(false, $result->isPartialMatch());
    }

    public function testReverseWithSubLocalityLevels()
    {
        $provider = $this->getGoogleMapsProvider();
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(36.2745084, 136.9003169));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertInstanceOf('\Geocoder\Model\AdminLevelCollection', $result->getSubLocalityLevels());
        $this->assertEquals('Iijima', $result->getSubLocalityLevels()->get(2)->getName());
        $this->assertEquals(false, $result->isPartialMatch());
    }

    public function testGeocodeWithPostalCodeSuffixComponent()
    {
        $provider = $this->getGoogleMapsProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('900 S Oak Park Ave, Oak Park, IL 60304'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals('900 S Oak Park Ave, Oak Park, IL 60304, USA', $result->getFormattedAddress());
        $this->assertEquals('1936', $result->getPostalCodeSuffix());
        $this->assertEquals(false, $result->isPartialMatch());
    }

    public function testGeocodeBoundsWithRealAddressWithViewportOnly()
    {
        $provider = $this->getGoogleMapsProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('Sibbe, Netherlands'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(50.8376, $result->getBounds()->getSouth(), 0.001);
        $this->assertEqualsWithDelta(5.8113, $result->getBounds()->getWest(), 0.001);
        $this->assertEqualsWithDelta(50.8517, $result->getBounds()->getNorth(), 0.001);
        $this->assertEqualsWithDelta(5.8433, $result->getBounds()->getEast(), 0.001);
        $this->assertEquals(false, $result->isPartialMatch());
    }

    public function testGeocodeDuplicateSubLocalityLevel()
    {
        $provider = $this->getGoogleMapsProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('Rue de Pont-A-Migneloux, 6210 Wayaux, Belgique'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var GoogleAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals('Rue de Pont-à-Migneloux, 6210 Les Bons Villers, Belgium', $result->getFormattedAddress());
        $this->assertEquals('Les Bons Villers', $result->getSubLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Région Wallonne', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Hainaut', $result->getAdminLevels()->get(2)->getName());
        $this->assertInstanceOf('\Geocoder\Model\AdminLevelCollection', $result->getSubLocalityLevels());
        $this->assertEquals(1, $result->getSubLocalityLevels()->get(1)->getLevel());
        $this->assertEquals('Wayaux / Les Bons Villers', $result->getSubLocalityLevels()->get(1)->getName());
        $this->assertEquals('Wayaux / Les Bons Villers', $result->getSubLocalityLevels()->get(1)->getCode());
    }

    private function getGoogleMapsProvider(): GoogleMaps
    {
        if (!isset($_SERVER['GOOGLE_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the GOOGLE_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new GoogleMaps(
            $this->getHttpClient($_SERVER['GOOGLE_GEOCODING_KEY']),
            null,
            $_SERVER['GOOGLE_GEOCODING_KEY']
        );

        return $provider;
    }
}
