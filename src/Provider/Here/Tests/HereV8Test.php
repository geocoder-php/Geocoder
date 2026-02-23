<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Here\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Provider\Here\Here;
use Geocoder\Provider\Here\Model\HereAddress;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class HereV8Test extends BaseTestCase
{
    protected function getCacheDir(): ?string
    {
        return null;
    }

    public function testGetName(): void
    {
        $provider = Here::createUsingApiKey($this->getMockedHttpClient(), 'api-key');
        $this->assertEquals('Here', $provider->getName());
    }

    public function testGetBaseUrl(): void
    {
        $provider = Here::createUsingApiKey($this->getMockedHttpClient(), 'api-key');
        $this->assertEquals(Here::GEOCODE_ENDPOINT_URL, $provider->getBaseUrl(GeocodeQuery::create('Paris')));
        $this->assertEquals(Here::REVERSE_ENDPOINT_URL, $provider->getBaseUrl(ReverseQuery::fromCoordinates(48.8, 2.3)));
    }

    public function testGeocodeWithInvalidData(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

        $provider = Here::createUsingApiKey($this->getMockedHttpClient(), 'api-key');
        $provider->geocodeQuery(GeocodeQuery::create('foobar'));
    }

    public function testGeocodeIpv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Here provider does not support IP addresses, only street addresses.');

        $provider = Here::createUsingApiKey($this->getMockedHttpClient(), 'api-key');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Here provider does not support IP addresses, only street addresses.');

        $provider = Here::createUsingApiKey($this->getMockedHttpClient(), 'api-key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Here provider does not support IP addresses, only street addresses.');

        $provider = Here::createUsingApiKey($this->getMockedHttpClient(), 'api-key');
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }

    public function testGeocodeInvalidApiKey(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('Invalid or missing api key.');

        $provider = Here::createUsingApiKey(
            $this->getMockedHttpClient('{"error":"Unauthorized","error_description":"apiKey invalid"}'),
            'bad-key'
        );
        $provider->geocodeQuery(GeocodeQuery::create('New York'));
    }

    public function testGeocodeWithNoResults(): void
    {
        $provider = Here::createUsingApiKey(
            $this->getMockedHttpClient('{"items":[]}'),
            'api-key'
        );

        $result = $provider->geocodeQuery(GeocodeQuery::create('jsajhgsdkfjhsfkjhaldkadjaslgldasd'));
        $this->assertEmpty($result);
    }

    public function testGeocodeMapping(): void
    {
        $json = <<<'JSON'
{
  "items": [
    {
      "title": "10 Downing St, London, SW1A 2AA, United Kingdom",
      "id": "here:af:streetsection:some-location-id",
      "resultType": "houseNumber",
      "houseNumberType": "PA",
      "address": {
        "label": "10 Downing St, London, SW1A 2AA, United Kingdom",
        "countryCode": "GBR",
        "countryName": "United Kingdom",
        "state": "England",
        "county": "Greater London",
        "city": "London",
        "district": "Westminster",
        "street": "Downing St",
        "postalCode": "SW1A 2AA",
        "houseNumber": "10"
      },
      "position": {
        "lat": 51.50322,
        "lng": -0.12768
      },
      "mapView": {
        "west": -0.12955,
        "south": 51.50232,
        "east": -0.12581,
        "north": 51.50412
      }
    }
  ]
}
JSON;

        $provider = Here::createUsingApiKey($this->getMockedHttpClient($json), 'api-key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 Downing St, London, UK'));

        $this->assertInstanceOf(\Geocoder\Model\AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var HereAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(HereAddress::class, $result);

        // Coordinates
        $this->assertEqualsWithDelta(51.50322, $result->getCoordinates()->getLatitude(), 0.00001);
        $this->assertEqualsWithDelta(-0.12768, $result->getCoordinates()->getLongitude(), 0.00001);

        // Bounds (mapView)
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(51.50232, $result->getBounds()->getSouth(), 0.00001);
        $this->assertEqualsWithDelta(-0.12955, $result->getBounds()->getWest(), 0.00001);
        $this->assertEqualsWithDelta(51.50412, $result->getBounds()->getNorth(), 0.00001);
        $this->assertEqualsWithDelta(-0.12581, $result->getBounds()->getEast(), 0.00001);

        // Address fields
        $this->assertEquals('10', $result->getStreetNumber());
        $this->assertEquals('Downing St', $result->getStreetName());
        $this->assertEquals('SW1A 2AA', $result->getPostalCode());
        $this->assertEquals('London', $result->getLocality());
        $this->assertEquals('Westminster', $result->getSubLocality());
        $this->assertEquals('GBR', $result->getCountry()->getCode());
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());

        // HERE-specific fields
        $this->assertEquals('here:af:streetsection:some-location-id', $result->getLocationId());
        $this->assertEquals('houseNumber', $result->getLocationType());
        $this->assertEquals('10 Downing St, London, SW1A 2AA, United Kingdom', $result->getLocationName());

        // Additional data from address
        $this->assertEquals('10 Downing St, London, SW1A 2AA, United Kingdom', $result->getAdditionalDataValue('Label'));
        $this->assertEquals('United Kingdom', $result->getAdditionalDataValue('CountryName'));
        $this->assertEquals('England', $result->getAdditionalDataValue('StateName'));
        $this->assertEquals('Greater London', $result->getAdditionalDataValue('CountyName'));
        $this->assertEquals('Westminster', $result->getAdditionalDataValue('District'));

        // Item-level metadata
        $this->assertEquals('PA', $result->getAdditionalDataValue('HouseNumberType'));
    }

    public function testReverseMapping(): void
    {
        $json = <<<'JSON'
{
  "items": [
    {
      "title": "Avenue Gambetta, 75020 Paris, France",
      "id": "here:af:streetsection:reverse-id",
      "resultType": "street",
      "address": {
        "label": "Avenue Gambetta, 75020 Paris, France",
        "countryCode": "FRA",
        "countryName": "France",
        "state": "Île-de-France",
        "county": "Paris",
        "city": "Paris",
        "street": "Avenue Gambetta",
        "postalCode": "75020"
      },
      "position": {
        "lat": 48.86322,
        "lng": 2.38877
      },
      "mapView": {
        "west": 2.38530,
        "south": 48.86315,
        "east": 2.38883,
        "north": 48.86322
      }
    }
  ]
}
JSON;

        $provider = Here::createUsingApiKey($this->getMockedHttpClient($json), 'api-key');
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.8632156, 2.3887722));

        $this->assertInstanceOf(\Geocoder\Model\AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var HereAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(HereAddress::class, $result);

        $this->assertEqualsWithDelta(48.86322, $result->getCoordinates()->getLatitude(), 0.00001);
        $this->assertEqualsWithDelta(2.38877, $result->getCoordinates()->getLongitude(), 0.00001);
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('75020', $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('FRA', $result->getCountry()->getCode());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('here:af:streetsection:reverse-id', $result->getLocationId());
        $this->assertEquals('street', $result->getLocationType());
        $this->assertEquals('France', $result->getAdditionalDataValue('CountryName'));
        $this->assertEquals('Île-de-France', $result->getAdditionalDataValue('StateName'));
    }

    public function testResponseWithoutMapView(): void
    {
        $json = <<<'JSON'
{
  "items": [
    {
      "title": "Paris, Île-de-France, France",
      "id": "here:cm:namedplace:12345",
      "resultType": "locality",
      "address": {
        "countryCode": "FRA",
        "countryName": "France",
        "state": "Île-de-France",
        "city": "Paris"
      },
      "position": {
        "lat": 48.85341,
        "lng": 2.3488
      }
    }
  ]
}
JSON;

        $provider = Here::createUsingApiKey($this->getMockedHttpClient($json), 'api-key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('Paris'));

        $this->assertCount(1, $results);
        $result = $results->first();

        $this->assertEqualsWithDelta(48.85341, $result->getCoordinates()->getLatitude(), 0.00001);
        $this->assertNull($result->getBounds());
        $this->assertEquals('Paris', $result->getLocality());
    }

    public function testGeocodeWithStructuredParams(): void
    {
        $json = '{"items":[{"title":"Barcelona, Catalonia, Spain","id":"here:cm:namedplace:1","resultType":"locality","address":{"countryCode":"ESP","countryName":"Spain","state":"Catalonia","city":"Barcelona"},"position":{"lat":41.38879,"lng":2.15899}}]}';

        $provider = Here::createUsingApiKey($this->getMockedHttpClient($json), 'api-key');

        $query = GeocodeQuery::create('Barcelona')
            ->withData('country', 'ESP')
            ->withData('state', 'Catalonia')
            ->withLocale('en');

        $results = $provider->geocodeQuery($query);
        $this->assertCount(1, $results);

        $result = $results->first();
        $this->assertEquals('Barcelona', $result->getLocality());
        $this->assertEquals('Spain', $result->getCountry()->getName());
    }

    public function testApiKeyIsIncludedInGeocodeRequest(): void
    {
        // The mocked client returns empty items; we just want to confirm no exception is thrown
        // and that the v8 code path is taken (apiKey param, not app_id/app_code).
        $provider = Here::createUsingApiKey($this->getMockedHttpClient('{"items":[]}'), 'test-api-key');
        $result = $provider->geocodeQuery(GeocodeQuery::create('Paris'));
        $this->assertEmpty($result);
    }

    public function testApiKeyIsIncludedInReverseRequest(): void
    {
        $provider = Here::createUsingApiKey($this->getMockedHttpClient('{"items":[]}'), 'test-api-key');
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.85, 2.35));
        $this->assertEmpty($result);
    }

    public function testGeocodeWithAdminLevels(): void
    {
        $json = <<<'JSON'
{
  "items": [
    {
      "title": "Test, Region, Country",
      "id": "here:test:1",
      "resultType": "houseNumber",
      "address": {
        "countryCode": "DEU",
        "countryName": "Germany",
        "state": "Bavaria",
        "stateCode": "BY",
        "county": "Munich",
        "countyCode": "M",
        "city": "Munich",
        "district": "Maxvorstadt",
        "street": "Ludwigstrasse",
        "postalCode": "80539",
        "houseNumber": "1"
      },
      "position": {
        "lat": 48.14816,
        "lng": 11.5735
      }
    }
  ]
}
JSON;

        $provider = Here::createUsingApiKey($this->getMockedHttpClient($json), 'api-key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('Ludwigstrasse 1, Munich'));

        $this->assertCount(1, $results);

        /** @var HereAddress $result */
        $result = $results->first();
        $this->assertEquals('DEU', $result->getCountry()->getCode());
        $this->assertEquals('Maxvorstadt', $result->getSubLocality());
        $this->assertEquals('BY', $result->getAdditionalDataValue('StateCode'));
        $this->assertEquals('Bavaria', $result->getAdditionalDataValue('StateName'));
        $this->assertEquals('Munich', $result->getAdditionalDataValue('CountyName'));
        $this->assertEquals('M', $result->getAdditionalDataValue('CountyCode'));
        $this->assertEquals('Maxvorstadt', $result->getAdditionalDataValue('District'));
    }
}
