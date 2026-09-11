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

use Geocoder\Exception\InvalidServerResponse;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\Bounds;
use Geocoder\Provider\Mapbox\Mapbox;
use Geocoder\Provider\Mapbox\Model\MapboxAddress;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Message\RequestInterface;

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

    private function assertRequestUrl(string $expectedUrl, callable $request): void
    {
        $uri = '';
        $client = $this->getMockedHttpClientCallback(function (RequestInterface $request) use (&$uri) {
            $uri = (string) $request->getUri();
        });

        try {
            $request($client);
        } catch (InvalidServerResponse $e) {
            // the mocked client answers an empty body; we only assert the request
        }

        $this->assertSame($expectedUrl, $uri);
    }

    public function testForwardGeocodeUrl(): void
    {
        $this->assertRequestUrl(
            'https://api.mapbox.com/search/geocode/v6/forward'
            .'?q=10+Downing+St%2C+London'
            .'&types=address'
            .'&limit=5'
            .'&access_token=access_token',
            static function ($client) {
                (new Mapbox($client, 'access_token'))->geocodeQuery(GeocodeQuery::create('10 Downing St, London'));
            }
        );
    }

    public function testForwardGeocodeUrlWithAllOptions(): void
    {
        $this->assertRequestUrl(
            'https://api.mapbox.com/search/geocode/v6/forward'
            .'?q=wahsington'
            .'&bbox=-124.83609163%2C45.54372254%2C-116.91742984%2C49.00243912'
            .'&types=address%2Cstreet'
            .'&autocomplete=true'
            .'&proximity=-120.09%2C47.60'
            .'&worldview=us'
            .'&country=US'
            .'&language=en'
            .'&limit=10'
            .'&permanent=true'
            .'&access_token=access_token',
            static function ($client) {
                $provider = new Mapbox($client, 'access_token', 'US', true);
                $query = GeocodeQuery::create('wahsington')
                    ->withLocale('en')
                    ->withBounds(new Bounds(45.54372254, -124.83609163, 49.00243912, -116.91742984))
                    ->withLimit(10)
                    ->withData('location_type', [Mapbox::TYPE_ADDRESS, Mapbox::TYPE_STREET])
                    ->withData('autocomplete', true)
                    ->withData('proximity', '-120.09,47.60')
                    ->withData('worldview', 'us');
                $provider->geocodeQuery($query);
            }
        );
    }

    public function testForwardGeocodeUrlWithStructuredInput(): void
    {
        // Structured Input: the query text is NOT sent; fields use their v6 names.
        // autocomplete defaults to false for structured input (Mapbox guidance).
        $this->assertRequestUrl(
            'https://api.mapbox.com/search/geocode/v6/forward'
            .'?address_number=2595'
            .'&street=Lucky+John+Dr'
            .'&place=Park+City'
            .'&region=UT'
            .'&postcode=84060'
            .'&types=address'
            .'&autocomplete=false'
            .'&country=US'
            .'&limit=5'
            .'&access_token=access_token',
            static function ($client) {
                $provider = new Mapbox($client, 'access_token');
                $query = GeocodeQuery::create('2595 Lucky John Dr, Park City, UT 84060')
                    ->withData('address_number', '2595')
                    ->withData('street', 'Lucky John Dr')
                    ->withData('place', 'Park City')
                    ->withData('region', 'UT')
                    ->withData('postcode', '84060')
                    ->withData('country', 'US');
                $provider->geocodeQuery($query);
            }
        );
    }

    public function testForwardGeocodeUrlWithStructuredInputAndExplicitAutocomplete(): void
    {
        // an explicit autocomplete value overrides the structured-input default
        $this->assertRequestUrl(
            'https://api.mapbox.com/search/geocode/v6/forward'
            .'?street=9th+Street'
            .'&types=address'
            .'&autocomplete=true'
            .'&limit=5'
            .'&access_token=access_token',
            static function ($client) {
                $provider = new Mapbox($client, 'access_token');
                $query = GeocodeQuery::create('9th Street')
                    ->withData('street', '9th Street')
                    ->withData('autocomplete', true);
                $provider->geocodeQuery($query);
            }
        );
    }

    public function testReverseGeocodeUrl(): void
    {
        $this->assertRequestUrl(
            'https://api.mapbox.com/search/geocode/v6/reverse'
            .'?longitude=2.388911'
            .'&latitude=48.8631507'
            .'&types=address'
            .'&limit=5'
            .'&access_token=access_token',
            static function ($client) {
                (new Mapbox($client, 'access_token'))->reverseQuery(ReverseQuery::fromCoordinates(48.8631507, 2.388911));
            }
        );
    }

    public function testReverseGeocodeUrlWithLocationTypeAndCountry(): void
    {
        $this->assertRequestUrl(
            'https://api.mapbox.com/search/geocode/v6/reverse'
            .'?longitude=2.388911'
            .'&latitude=48.8631507'
            .'&types=address%2Cpostcode'
            .'&country=FR'
            .'&limit=2'
            .'&access_token=access_token',
            static function ($client) {
                $provider = new Mapbox($client, 'access_token');
                $query = ReverseQuery::fromCoordinates(48.8631507, 2.388911)
                    ->withData('location_type', [Mapbox::TYPE_ADDRESS, Mapbox::TYPE_POSTCODE])
                    ->withData('country', 'FR')
                    ->withLimit(2);
                $provider->reverseQuery($query);
            }
        );
    }

    public function testParseForwardAddressFeature(): void
    {
        $json = <<<'JSON'
        {
            "type": "FeatureCollection",
            "features": [
                {
                    "type": "Feature",
                    "id": "dXJuOm1ieGFkcjo3Njg3YjZmNy01YmZkLTQzMjItOGMzOS02OGE1NDhmZWYwM2U",
                    "geometry": { "type": "Point", "coordinates": [-122.413709, 37.7757] },
                    "properties": {
                        "mapbox_id": "dXJuOm1ieGFkcjo3Njg3YjZmNy01YmZkLTQzMjItOGMzOS02OGE1NDhmZWYwM2U",
                        "feature_type": "address",
                        "name": "149 9th Street",
                        "full_address": "149 9th Street, San Francisco, California 94103, United States",
                        "coordinates": { "longitude": -122.413709, "latitude": 37.7757, "accuracy": "rooftop" },
                        "match_code": { "address_number": "matched", "street": "matched", "confidence": "exact" },
                        "context": {
                            "address": { "address_number": "149", "street_name": "9th Street", "name": "149 9th Street" },
                            "street": { "name": "9th Street" },
                            "neighborhood": { "name": "South of Market" },
                            "postcode": { "name": "94103" },
                            "place": { "name": "San Francisco" },
                            "region": { "name": "California", "region_code": "CA", "region_code_full": "US-CA" },
                            "country": { "name": "United States", "country_code": "US", "country_code_alpha_3": "USA" }
                        }
                    }
                }
            ]
        }
        JSON;

        $provider = new Mapbox($this->getMockedHttpClient($json), 'access_token');
        $results = $provider->geocodeQuery(GeocodeQuery::create('149 9th St, San Francisco, CA 94103'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var MapboxAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(MapboxAddress::class, $result);
        $this->assertEqualsWithDelta(37.7757, $result->getCoordinates()->getLatitude(), 0.00001);
        $this->assertEqualsWithDelta(-122.413709, $result->getCoordinates()->getLongitude(), 0.00001);
        $this->assertEquals('dXJuOm1ieGFkcjo3Njg3YjZmNy01YmZkLTQzMjItOGMzOS02OGE1NDhmZWYwM2U', $result->getId());
        $this->assertEquals('9th Street', $result->getStreetName());
        $this->assertEquals('149', $result->getStreetNumber());
        $this->assertEquals('94103', $result->getPostalCode());
        $this->assertEquals('San Francisco', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('San Francisco', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('California', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('CA', $result->getAdminLevels()->get(2)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('149 9th Street, San Francisco, California 94103, United States', $result->getFormattedAddress());
        $this->assertEquals('South of Market', $result->getNeighborhood());
        $this->assertEquals(['address'], $result->getResultType());
        $this->assertEquals('exact', $result->getMatchConfidence());
        $this->assertEquals('matched', $result->getMatchCode()['street']);
        $this->assertEquals('rooftop', $result->getAccuracy());
        $this->assertNull($result->getTimezone());
    }

    public function testParseForwardPlaceFeature(): void
    {
        // place feature: no context.address; name is the place name; region code may be 3 letters
        $json = <<<'JSON'
        {
            "type": "FeatureCollection",
            "features": [
                {
                    "type": "Feature",
                    "id": "dXJuOm1ieHBsYzpoMmhQ",
                    "geometry": { "type": "Point", "coordinates": [-0.83069, 51.724422] },
                    "properties": {
                        "mapbox_id": "dXJuOm1ieHBsYzpoMmhQ",
                        "feature_type": "place",
                        "name": "Princes Risborough",
                        "full_address": "Princes Risborough, Buckinghamshire, Inghilterra, Regno Unito",
                        "coordinates": { "longitude": -0.83069, "latitude": 51.724422 },
                        "context": {
                            "place": { "name": "Princes Risborough" },
                            "region": { "name": "Inghilterra", "region_code": "ENG" },
                            "country": { "name": "Regno Unito", "country_code": "GB" }
                        }
                    }
                }
            ]
        }
        JSON;

        $provider = new Mapbox($this->getMockedHttpClient($json), 'access_token');
        $results = $provider->geocodeQuery(GeocodeQuery::create('princ'));

        $this->assertCount(1, $results);

        /** @var MapboxAddress $result */
        $result = $results->first();
        $this->assertEquals('dXJuOm1ieHBsYzpoMmhQ', $result->getId());
        $this->assertEquals('Princes Risborough', $result->getStreetName());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Princes Risborough', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Princes Risborough', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Inghilterra', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('ENG', $result->getAdminLevels()->get(2)->getCode());
        $this->assertEquals('Regno Unito', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountry()->getCode());
        $this->assertEquals('Princes Risborough, Buckinghamshire, Inghilterra, Regno Unito', $result->getFormattedAddress());
        $this->assertNull($result->getNeighborhood());
        $this->assertEquals(['place'], $result->getResultType());
        $this->assertNull($result->getMatchConfidence());
        $this->assertNull($result->getMatchCode());
        $this->assertNull($result->getAccuracy());
    }

    public function testParseForwardStreetFeature(): void
    {
        // v6 street feature: street name comes from context.street
        $json = <<<'JSON'
        {
            "type": "FeatureCollection",
            "features": [
                {
                    "type": "Feature",
                    "id": "address.7607992201284362",
                    "geometry": { "type": "Point", "coordinates": [-117.039767, 46.930253] },
                    "properties": {
                        "mapbox_id": "address.7607992201284362",
                        "feature_type": "street",
                        "name": "Washington Highway 6",
                        "full_address": "Washington Highway 6, Palouse, Washington 99161, United States",
                        "coordinates": { "longitude": -117.039767, "latitude": 46.930253 },
                        "context": {
                            "street": { "name": "Washington Highway 6" },
                            "postcode": { "name": "99161" },
                            "place": { "name": "Palouse" },
                            "region": { "name": "Washington", "region_code": "WA" },
                            "country": { "name": "United States", "country_code": "US" }
                        }
                    }
                }
            ]
        }
        JSON;

        $provider = new Mapbox($this->getMockedHttpClient($json), 'access_token');
        $results = $provider->geocodeQuery(GeocodeQuery::create('Washington Highway 6'));

        $this->assertCount(1, $results);

        /** @var MapboxAddress $result */
        $result = $results->first();
        $this->assertEquals('address.7607992201284362', $result->getId());
        $this->assertEquals('Washington Highway 6', $result->getStreetName());
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('99161', $result->getPostalCode());
        $this->assertEquals('Palouse', $result->getLocality());
        $this->assertEquals('Washington', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('WA', $result->getAdminLevels()->get(2)->getCode());
        $this->assertEquals(['street'], $result->getResultType());
    }

    public function testParseFeatureWithoutContext(): void
    {
        // features without a context object must still parse (name + coordinates)
        $json = <<<'JSON'
        {
            "type": "FeatureCollection",
            "features": [
                {
                    "type": "Feature",
                    "id": "abc123",
                    "geometry": { "type": "Point", "coordinates": [2.35, 48.85] },
                    "properties": {
                        "mapbox_id": "abc123",
                        "feature_type": "place",
                        "name": "Some Place",
                        "coordinates": { "longitude": 2.35, "latitude": 48.85 }
                    }
                }
            ]
        }
        JSON;

        $provider = new Mapbox($this->getMockedHttpClient($json), 'access_token');
        $results = $provider->geocodeQuery(GeocodeQuery::create('Some Place'));

        $this->assertCount(1, $results);

        /** @var MapboxAddress $result */
        $result = $results->first();
        $this->assertEquals('Some Place', $result->getStreetName());
        $this->assertEquals('abc123', $result->getId());
        $this->assertEqualsWithDelta(48.85, $result->getCoordinates()->getLatitude(), 0.00001);
        $this->assertEqualsWithDelta(2.35, $result->getCoordinates()->getLongitude(), 0.00001);
    }

    public function testParseEmptyFeatures(): void
    {
        $json = '{"type":"FeatureCollection","features":[]}';

        $provider = new Mapbox($this->getMockedHttpClient($json), 'access_token');
        $results = $provider->geocodeQuery(GeocodeQuery::create('jsajhgsdkfjhsfkjhaldkadjaslgldasd'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertEmpty($results);
    }

    public function testParseInvalidJson(): void
    {
        $this->expectException(InvalidServerResponse::class);

        $provider = new Mapbox($this->getMockedHttpClient('{invalid json'), 'access_token');
        $provider->geocodeQuery(GeocodeQuery::create('10 Downing St, London'));
    }

    public function testGeocodeWithRealAddress(): void
    {
        if (!isset($_SERVER['MAPBOX_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPBOX_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new Mapbox($this->getHttpClient($_SERVER['MAPBOX_GEOCODING_KEY']), $_SERVER['MAPBOX_GEOCODING_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('149 9th St, San Francisco, CA 94103'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var MapboxAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(MapboxAddress::class, $result);
        $this->assertEqualsWithDelta(37.7757, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-122.413709, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertEquals('9th Street', $result->getStreetName());
        $this->assertEquals('149', $result->getStreetNumber());
        $this->assertEquals('94103', $result->getPostalCode());
        $this->assertEquals('San Francisco', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('California', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('CA', $result->getAdminLevels()->get(2)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('149 9th Street, San Francisco, California 94103, United States', $result->getFormattedAddress());
        $this->assertEquals('South of Market', $result->getNeighborhood());
        $this->assertEquals(['address'], $result->getResultType());
        $this->assertEquals('exact', $result->getMatchConfidence());
        $this->assertEquals('rooftop', $result->getAccuracy());

        // not provided
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealAddressInDc(): void
    {
        if (!isset($_SERVER['MAPBOX_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPBOX_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new Mapbox($this->getHttpClient($_SERVER['MAPBOX_GEOCODING_KEY']), $_SERVER['MAPBOX_GEOCODING_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('1600 Pennsylvania Avenue NW, Washington, DC 20500'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(2, $results);

        /** @var MapboxAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(MapboxAddress::class, $result);
        $this->assertEqualsWithDelta(38.897684, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-77.036574, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertEquals('Pennsylvania Avenue Northwest', $result->getStreetName());
        $this->assertEquals('1600', $result->getStreetNumber());
        $this->assertEquals('20500', $result->getPostalCode());
        $this->assertEquals('Washington', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('District of Columbia', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('DC', $result->getAdminLevels()->get(2)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('1600 Pennsylvania Avenue Northwest, Washington, District of Columbia 20500, United States', $result->getFormattedAddress());
        $this->assertEquals('exact', $result->getMatchConfidence());
        $this->assertEquals('rooftop', $result->getAccuracy());
    }

    public function testReverseWithRealCoordinates(): void
    {
        if (!isset($_SERVER['MAPBOX_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPBOX_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new Mapbox($this->getHttpClient($_SERVER['MAPBOX_GEOCODING_KEY']), $_SERVER['MAPBOX_GEOCODING_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.8631507, 2.388911));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(4, $results);

        /** @var MapboxAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(MapboxAddress::class, $result);
        $this->assertEquals('12', $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('75020', $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Île-de-France', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('IDF', $result->getAdminLevels()->get(2)->getCode());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
        $this->assertEquals('12 Avenue Gambetta, 75020 Paris, France', $result->getFormattedAddress());
        $this->assertEquals('rooftop', $result->getAccuracy());

        // not provided by reverse geocoding
        $this->assertNull($result->getMatchConfidence());
        $this->assertNull($result->getNeighborhood());
    }

    public function testGeocodePlaceWithLocale(): void
    {
        if (!isset($_SERVER['MAPBOX_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPBOX_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new Mapbox($this->getHttpClient($_SERVER['MAPBOX_GEOCODING_KEY']), $_SERVER['MAPBOX_GEOCODING_KEY']);

        $query = GeocodeQuery::create('princ');
        $query = $query->withLocale('it');
        $query = $query->withBounds(new Bounds(
            35.82809688193029,
            -11.36323261153737,
            59.05992036364424,
            34.33947713277206
        ));
        $query = $query->withLimit(1);
        $query = $query->withData('location_type', [
            Mapbox::TYPE_PLACE,
            Mapbox::TYPE_LOCALITY,
            Mapbox::TYPE_NEIGHBORHOOD,
        ]);

        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var MapboxAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(MapboxAddress::class, $result);
        $this->assertEqualsWithDelta(51.724422, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-0.83069, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertEquals('Princes Risborough', $result->getStreetName());
        $this->assertEquals(['place'], $result->getResultType());
        $this->assertEquals('Princes Risborough', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Princes Risborough', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Inghilterra', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('ENG', $result->getAdminLevels()->get(2)->getCode());
        $this->assertEquals('Regno Unito', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getMatchConfidence());
    }

    public function testGeocodeWithAutocompleteEnabled(): void
    {
        if (!isset($_SERVER['MAPBOX_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPBOX_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new Mapbox($this->getHttpClient($_SERVER['MAPBOX_GEOCODING_KEY']), $_SERVER['MAPBOX_GEOCODING_KEY']);

        $query = GeocodeQuery::create('washi');
        $query = $query->withData('autocomplete', true);
        $query = $query->withBounds(new Bounds(
            45.54372254,
            -124.83609163,
            49.00243912,
            -116.91742984
        ));
        $query = $query->withData('location_type', [
            Mapbox::TYPE_REGION,
            Mapbox::TYPE_NEIGHBORHOOD,
            Mapbox::TYPE_STREET,
            Mapbox::TYPE_PLACE,
            Mapbox::TYPE_LOCALITY,
        ]);

        $results = $provider->geocodeQuery($query);
        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var MapboxAddress $first */
        $first = $results->first();
        $this->assertEquals(['region'], $first->getResultType());
        $this->assertEquals('Washington', $first->getStreetName());
        $this->assertEquals('Washington', $first->getAdminLevels()->get(2)->getName());
        $this->assertEquals('WA', $first->getAdminLevels()->get(2)->getCode());
    }

    public function testGeocodeWithAutocompleteDisabled(): void
    {
        if (!isset($_SERVER['MAPBOX_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPBOX_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new Mapbox($this->getHttpClient($_SERVER['MAPBOX_GEOCODING_KEY']), $_SERVER['MAPBOX_GEOCODING_KEY']);

        $query = GeocodeQuery::create('washi');
        $query = $query->withData('autocomplete', false);
        $query = $query->withBounds(new Bounds(
            45.54372254,
            -124.83609163,
            49.00243912,
            -116.91742984
        ));
        $query = $query->withData('location_type', [
            Mapbox::TYPE_REGION,
            Mapbox::TYPE_NEIGHBORHOOD,
            Mapbox::TYPE_STREET,
            Mapbox::TYPE_PLACE,
            Mapbox::TYPE_LOCALITY,
        ]);

        $results = $provider->geocodeQuery($query);
        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var MapboxAddress $first */
        $first = $results->first();
        $this->assertEquals(['street'], $first->getResultType());
        $this->assertEquals('Yashi Road', $first->getStreetName());
    }

    public function testGeocodeWithStructuredInput(): void
    {
        if (!isset($_SERVER['MAPBOX_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPBOX_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new Mapbox($this->getHttpClient($_SERVER['MAPBOX_GEOCODING_KEY']), $_SERVER['MAPBOX_GEOCODING_KEY']);

        $query = GeocodeQuery::create('2595 Lucky John Dr, Park City, UT 84060');
        $query = $query->withData('address_number', '2595');
        $query = $query->withData('street', 'Lucky John Dr');
        $query = $query->withData('place', 'Park City');
        $query = $query->withData('region', 'UT');
        $query = $query->withData('postcode', '84060');

        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var MapboxAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(MapboxAddress::class, $result);
        $this->assertEqualsWithDelta(40.6716, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-111.507008, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertEquals('Lucky John Drive', $result->getStreetName());
        $this->assertEquals('2595', $result->getStreetNumber());
        $this->assertEquals('84060', $result->getPostalCode());
        $this->assertEquals('Park City', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Park City', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Utah', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('UT', $result->getAdminLevels()->get(2)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('2595 Lucky John Drive, Park City, Utah 84060, United States', $result->getFormattedAddress());
        $this->assertEquals('Park Meadows', $result->getNeighborhood());
        $this->assertEquals('exact', $result->getMatchConfidence());
        $this->assertEquals('rooftop', $result->getAccuracy());
    }
}
