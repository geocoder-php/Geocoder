<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * @author Sébastien Barré <sebastien@sheub.eu>
 */

namespace Geocoder\Provider\Here\Tests;

use Geocoder\Exception\Exception;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\Coordinates;
use Geocoder\Provider\Here\Here;
use Geocoder\Provider\Here\Model\HereAddress;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

/**
 * @group here
 * @group here-unit
 */
class HereTest extends BaseTestCase
{
    protected function getCacheDir(): ?string
    {
        if (isset($_SERVER['USE_CACHED_RESPONSES']) && true === $_SERVER['USE_CACHED_RESPONSES']) {
            return __DIR__.'/.cached_responses';
        }

        return null;
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testGeocodeWithRealAddress(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France')->withLocale('fr-FR'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $this->assertInstanceOf(Address::class, $result);
        $this->assertEqualsWithDelta(48.8653, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.39844, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(48.8664242, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(2.3967311, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(48.8641758, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(2.4001489, $result->getBounds()->getEast(), 0.01);
        $this->assertEquals(10, $result->getStreetNumber());

        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FRA', $result->getCountry()->getCode());
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testGeocodeWithQualifiedQuery(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $query = GeocodeQuery::create('houseNumber=405;street=Urban St;city=lakewood;state=CO;postalCode=80228;country=UnitedStates')
                             ->withData('qq', true);

        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var HereAddress $result */
        $result = $results->first();

        $this->assertInstanceOf(Address::class, $result);
        $this->assertEqualsWithDelta(39.72362, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-105.13472, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(-105.13589, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(39.72272, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(-105.13355, $result->getBounds()->getEast(), 0.01);
        $this->assertEqualsWithDelta(39.72452, $result->getBounds()->getNorth(), 0.01);

        $this->assertEquals('80228-1205', $result->getPostalCode());
        $this->assertEquals('Union Square', $result->getSubLocality());
        $this->assertEquals('Lakewood', $result->getLocality());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testGeocodeWithCenterOn(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $results = $provider->geocodeQuery(
            GeocodeQuery::create('405 Urban St')
            ->withData('centerOn', [
                'lat' => 39.72344,
                'lon' => -105.13392,
            ])
        );

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var HereAddress $result */
        $result = $results->first();

        $this->assertInstanceOf(Address::class, $result);
        $this->assertEqualsWithDelta(39.72362, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-105.13472, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(-105.13589, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(39.72272, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(-105.13355, $result->getBounds()->getEast(), 0.01);
        $this->assertEqualsWithDelta(39.72452, $result->getBounds()->getNorth(), 0.01);

        $this->assertEquals('80228-1205', $result->getPostalCode());
        $this->assertEquals('Union Square', $result->getSubLocality());
        $this->assertEquals('Lakewood', $result->getLocality());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeWithCenterOnWithTwoManyCoordinates(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Expected a set of 2 coordinates got 4');

        $provider->geocodeQuery(
            GeocodeQuery::create('405 Urban St')
                        ->withData('centerOn', [
                            'lat' => 39.72344,
                            'lon' => -105.13392,
                            'lat2' => 39.72344,
                            'lon2' => -105.13392,
                        ])
        );
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testGeocodeWithInString(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $queryBarcelonaFromSpain = GeocodeQuery::create('Barcelona')->withData('in', 'ESP')->withLocale('ca');
        $queryBarcelonaFromVenezuela = GeocodeQuery::create('Barcelona')->withData('in', 'VEN')->withLocale('ca');

        $resultsSpain = $provider->geocodeQuery($queryBarcelonaFromSpain);
        $resultsVenezuela = $provider->geocodeQuery($queryBarcelonaFromVenezuela);

        $this->assertInstanceOf(AddressCollection::class, $resultsSpain);
        $this->assertInstanceOf(AddressCollection::class, $resultsVenezuela);
        $this->assertCount(1, $resultsSpain);
        $this->assertCount(1, $resultsVenezuela);

        $resultSpain = $resultsSpain->first();
        $resultVenezuela = $resultsVenezuela->first();

        $this->assertEquals('Barcelona', $resultSpain->getLocality());
        $this->assertEquals('Barcelona', $resultVenezuela->getLocality());
        $this->assertEquals('Espanya', $resultSpain->getCountry()->getName());
        $this->assertEquals('Venezuela', $resultVenezuela->getCountry()->getName());
        $this->assertEquals('ESP', $resultSpain->getCountry()->getCode());
        $this->assertEquals('VEN', $resultVenezuela->getCountry()->getCode());
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testGeocodeWithInArray(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $query = GeocodeQuery::create('Barcelona')->withData('in', ['VEN', 'ESP']);

        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $this->assertEquals('Barcelona', $result->getLocality());
        $this->assertEquals('Espanya', $result->getCountry()->getName());
        $this->assertEquals('ESP', $result->getCountry()->getCode());
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeWithInvalidIn(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $query = GeocodeQuery::create('Barcelona')->withData('in', 1.0);

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Expected a string or an array of country codes');

        $provider->geocodeQuery($query);
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeWithLimit(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $query = GeocodeQuery::create('Union')->withData('limit', 2);

        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(2, $results);
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeWithNegativeInvalidLimit(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $query = GeocodeQuery::create('Union')->withData('limit', -1);

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('-1 is not a valid value');

        $provider->geocodeQuery($query);
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeWithOvermaxInvalidLimit(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $query = GeocodeQuery::create('Union')->withData('limit', 101);

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('101 is not a valid value');

        $provider->geocodeQuery($query);
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testGeocodeWithTypes(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $query = GeocodeQuery::create('405 Urban St Lakewood Colorado')->withData('types', ['area']);

        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $this->assertEquals('locality', $result->getLocationType());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals('Lakewood', $result->getLocality());
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeWithInvalidTypes(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('"notAType" is not a valid type');

        $query = GeocodeQuery::create('405 Urban St Lakewood Colorado')->withData('types', ['notAType']);

        $provider->geocodeQuery($query);
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testGeocodeWithPoliticalView(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);
        $kosovoQuery = GeocodeQuery::create('M5C7+Q59, Vëllezërit Fazliu, Prishtina')->withLocale('USA');
        $serbQuery = GeocodeQuery::create('M5C7+Q59, Vëllezërit Fazliu, Prishtina')
            ->withData('politicalView', 'SRB')
            ->withLocale('USA');

        $results = $provider->geocodeQuery($kosovoQuery);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $this->assertEquals('KOS', $result->getCountry()->getCode());

        $results = $provider->geocodeQuery($serbQuery);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $this->assertEquals('SRB', $result->getCountry()->getCode());
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeWithInvalidPoliticalView(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);
        $query = GeocodeQuery::create('M5C7+Q59, Vëllezërit Fazliu, Prishtina')
                                 ->withData('politicalView', 'notAView')
                                 ->withLocale('USA');

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Political view for "notAView" is not a supported');

        $provider->geocodeQuery($query);
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testGeocodeWithShowParams(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);
        $query = GeocodeQuery::create('405 Urban St Lakewood Colorado')->withData('show', Here::GEOCODE_SHOW_PARAMS);

        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $this->assertEquals('America/Denver', $result->getTimezone());

        $additionalData = $result->getAdditionalData();

        $this->assertEquals(['alpha2' => 'US', 'alpha3' => 'USA'], $additionalData['countryInfo']);

        $this->assertNotNull($additionalData['parsing']);
        $this->assertEquals(
            [
                [
                    'baseName' => 'Urban',
                    'streetType' => 'St',
                    'streetTypePrecedes' => false,
                    'streetTypeAttached' => false,
                    'language' => 'en',
                ],
            ],
            $additionalData['streetInfo']
        );
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeWithShowParamsNotArray(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);
        $query = GeocodeQuery::create('405 Urban St Lakewood Colorado')
                             ->withData('show', 'notAnArray');

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Show param(s) must be an array');

        $provider->geocodeQuery($query);
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeWithInvalidShowParams(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);
        $query = GeocodeQuery::create('405 Urban St Lakewood Colorado')
            ->withData('show', \array_merge(Here::GEOCODE_SHOW_PARAMS, ['notAParam']));

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Show param(s) "notAParam" are invalid');

        $provider->geocodeQuery($query);
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testGeocodeWithShowMapReferenceParams(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);
        $query = GeocodeQuery::create('405 Urban St Lakewood Colorado')
                                ->withData('showMapReferences', Here::GEOCODE_SHOW_MAP_REFERENCE_PARAMS);

        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $additionalData = $result->getAdditionalData();

        $this->assertNotNull($additionalData['mapReferences']);
        $this->assertArrayHasKey('pointAddress', $additionalData['mapReferences']);
        $this->assertArrayHasKey('segments', $additionalData['mapReferences']);
        $this->assertArrayHasKey('country', $additionalData['mapReferences']);
        $this->assertArrayHasKey('state', $additionalData['mapReferences']);
        $this->assertArrayHasKey('county', $additionalData['mapReferences']);
        $this->assertArrayHasKey('city', $additionalData['mapReferences']);
        $this->assertArrayHasKey('district', $additionalData['mapReferences']);
        $this->assertArrayHasKey('cmVersion', $additionalData['mapReferences']);
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeWithShowMapReferenceParamsNotArray(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);
        $query = GeocodeQuery::create('405 Urban St Lakewood Colorado')
                             ->withData('showMapReferences', 'notAnArray');

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Show map reference preference(s) must be an array');

        $provider->geocodeQuery($query);
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeWithInvalidShowMapReferenceParams(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);
        $query = GeocodeQuery::create('405 Urban St Lakewood Colorado')
                             ->withData('showMapReferences', \array_merge(Here::GEOCODE_SHOW_MAP_REFERENCE_PARAMS, ['notAParam']));

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Show map reference param(s) "notAParam" are invalid');

        $provider->geocodeQuery($query);
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testGeocodeWithShowNavAttributesParams(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);
        $query = GeocodeQuery::create('405 Urban St Lakewood Colorado')
                                ->withData('showNavAttributes', Here::GEOCODE_SHOW_NAV_ATTRIBUTES);

        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $additionalData = $result->getAdditionalData();

        $this->assertNotNull($additionalData['navigationAttributes']);
        $this->assertArrayHasKey('functionalClass', $additionalData['navigationAttributes']);
        $this->assertArrayHasKey('access', $additionalData['navigationAttributes']);
        $this->assertArrayHasKey('physical', $additionalData['navigationAttributes']);
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeWithShowNavAttributesNotArray(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);
        $query = GeocodeQuery::create('405 Urban St Lakewood Colorado')
                             ->withData('showNavAttributes', 'notAnArray');

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Show map nav attribute param(s) must be an array');

        $provider->geocodeQuery($query);
    }

    public function testGeocodeWithInvalidShowNavAttributesParams(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);
        $query = GeocodeQuery::create('405 Urban St Lakewood Colorado')
                             ->withData('showNavAttributes', \array_merge(Here::GEOCODE_SHOW_NAV_ATTRIBUTES, ['notAParam']));

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Show map nav attribute param(s) "notAParam" are invalid');

        $provider->geocodeQuery($query);
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testReverseWithRealCoordinates(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.8632156, 2.3887722));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEqualsWithDelta(48.8632156, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(2.3887722, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(2.3875, $result->getBounds()->getWest(), 0.001);
        $this->assertEqualsWithDelta(48.86294, $result->getBounds()->getSouth(), 0.001);
        $this->assertEqualsWithDelta(2.39555, $result->getBounds()->getEast(), 0.001);
        $this->assertEqualsWithDelta(48.86499, $result->getBounds()->getNorth(), 0.001);
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FRA', $result->getCountry()->getCode());
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testReverseWithBearing(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632157, 2.3887722)
            ->withData('bearing', 180);

        $results = $provider->reverseQuery($reverseQuery);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $this->assertEquals('street', $result->getLocationType());
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('Paris', $result->getLocality());
    }

    /**
     * @throws \JsonException
     */
    public function testReverseWithInvalidBearing(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Bearing must be between 0 and 359 degrees');

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632157, 2.3887722)
                                    ->withData('bearing', 99999);

        $provider->reverseQuery($reverseQuery);
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testReverseWithIn(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::create(new Coordinates(48.87, 2.38))
            ->withData('in', ['radius' => 10]);

        $results = $provider->reverseQuery($reverseQuery);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEqualsWithDelta(48.85717, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(2.3414, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(2.22383, $result->getBounds()->getWest(), 0.001);
        $this->assertEqualsWithDelta(48.81571, $result->getBounds()->getSouth(), 0.001);
        $this->assertEqualsWithDelta(2.4698, $result->getBounds()->getEast(), 0.001);
        $this->assertEqualsWithDelta(48.90248, $result->getBounds()->getNorth(), 0.001);
        $this->assertNull($result->getStreetName());
        $this->assertEquals(75001, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FRA', $result->getCountry()->getCode());
    }

    /**
     * @throws \JsonException
     */
    public function testReverseWithInvalidIn(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('In requires radius');

        $reverseQuery = ReverseQuery::create(new Coordinates(48.87, 2.38))
                                    ->withData('in', ['blah' => 'notARadius']);

        $provider->reverseQuery($reverseQuery);
    }

    /**
     * @throws \JsonException
     */
    public function testReverseWithLimit(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632156, 2.3887722)
            ->withData('limit', 2);

        $results = $provider->reverseQuery($reverseQuery);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(2, $results);
    }

    /**
     * @throws \JsonException
     */
    public function testReverseWithNegativeInvalidLimit(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632156, 2.3887722)
        ->withData('limit', -1);

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('-1 is not a valid value');

        $provider->reverseQuery($reverseQuery);
    }

    /**
     * @throws \JsonException
     */
    public function testReverseWithOverMaxInvalidLimit(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632156, 2.3887722)
                                    ->withData('limit', 101);

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('101 is not a valid value');

        $provider->reverseQuery($reverseQuery);
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testReverseWithTypes(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632156, 2.3887722)
            ->withData('types', ['city']);

        $results = $provider->reverseQuery($reverseQuery);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $this->assertEquals('locality', $result->getLocationType());
        $this->assertEquals('Paris', $result->getLocality());
    }

    /**
     * @throws \JsonException
     */
    public function testReverseWithInvalidTypes(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632156, 2.3887722)
                                    ->withData('types', ['notAType']);

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('"notAType" is not a valid type');

        $provider->reverseQuery($reverseQuery);
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testReverseWithLocale(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(42.66122, 21.14459);

        $results = $provider->reverseQuery($reverseQuery);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $this->assertEquals('Kosovë', $result->getCountry());

        $reverseQuery = $reverseQuery->withLocale('en');

        $results = $provider->reverseQuery($reverseQuery);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $this->assertEquals('Kosovo', $result->getCountry());
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testReverseWithPoliticalView(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(42.66122, 21.14448);

        $results = $provider->reverseQuery($reverseQuery);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $this->assertEquals('Kosovë', $result->getCountry()->getName());

        $reverseQuery = $reverseQuery->withData('politicalView', 'SRB');

        $results = $provider->reverseQuery($reverseQuery);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $this->assertEquals('Србија', $result->getCountry()->getName());
    }

    /**
     * @throws \JsonException
     */
    public function testReverseWithInvalidPoliticalView(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(42.66122, 21.14448)
            ->withData('politicalView', 'notAView');

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Political view for "notAView" is not a supported');

        $provider->reverseQuery($reverseQuery);
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testReverseWithShow(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632156, 2.3887722)
            ->withData('show', Here::REV_GEOCODE_SHOW_PARAMS);

        $results = $provider->reverseQuery($reverseQuery);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $this->assertEquals('Europe/Paris', $result->getTimezone());

        $additionalData = $result->getAdditionalData();

        $this->assertEquals(['alpha2' => 'FR', 'alpha3' => 'FRA'], $additionalData['countryInfo']);

        $this->assertEquals(
            [
                [
                    'baseName' => 'Gambetta',
                    'streetType' => 'Avenue',
                    'streetTypePrecedes' => true,
                    'streetTypeAttached' => false,
                    'language' => 'fr',
                ],
            ],
            $additionalData['streetInfo']
        );
    }

    /**
     * @throws \JsonException
     */
    public function testReverseWithShowNotArray(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632156, 2.3887722)
                                    ->withData('show', 'tz');

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Show param(s) must be an array');

        $provider->reverseQuery($reverseQuery);
    }

    /**
     * @throws \JsonException
     */
    public function testReverseWithInvalidShowParams(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632156, 2.3887722)
                                    ->withData('show', ['notAParam']);

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Show param(s) "notAParam" are invalid');

        $provider->reverseQuery($reverseQuery);
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testReverseWithShowMapReferences(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632156, 2.3887722)
            ->withData('showMapReferences', Here::GEOCODE_SHOW_MAP_REFERENCE_PARAMS);

        $results = $provider->reverseQuery($reverseQuery);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $additionalData = $result->getAdditionalData();

        $this->assertNotNull($additionalData['mapReferences']);
        $this->assertArrayHasKey('pointAddress', $additionalData['mapReferences']);
        $this->assertArrayHasKey('segments', $additionalData['mapReferences']);
        $this->assertArrayHasKey('country', $additionalData['mapReferences']);
        $this->assertArrayHasKey('state', $additionalData['mapReferences']);
        $this->assertArrayHasKey('county', $additionalData['mapReferences']);
        $this->assertArrayHasKey('city', $additionalData['mapReferences']);
        $this->assertArrayHasKey('district', $additionalData['mapReferences']);
        $this->assertArrayHasKey('cmVersion', $additionalData['mapReferences']);
    }

    /**
     * @throws \JsonException
     */
    public function testReverseWithShowMapReferencesNotArray(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632156, 2.3887722)
                                    ->withData('showMapReferences', 'adminIds');

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Show map reference preference(s) must be an array');

        $provider->reverseQuery($reverseQuery);
    }

    /**
     * @throws \JsonException
     */
    public function testReverseWithInvalidShowMapReferences(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632156, 2.3887722)
                                    ->withData('showMapReferences', ['notAParam']);

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Show map reference param(s) "notAParam" are invalid');

        $provider->reverseQuery($reverseQuery);
    }

    /**
     * @throws Exception|\JsonException
     */
    public function testReverseWithShowNavAttributes(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632156, 2.3887722)
            ->withData('showNavAttributes', Here::GEOCODE_SHOW_NAV_ATTRIBUTES);

        $results = $provider->reverseQuery($reverseQuery);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        $result = $results->first();

        $additionalData = $result->getAdditionalData();

        $this->assertNotNull($additionalData['navigationAttributes']);
        $this->assertArrayHasKey('functionalClass', $additionalData['navigationAttributes']);
        $this->assertArrayHasKey('access', $additionalData['navigationAttributes']);
        $this->assertArrayHasKey('physical', $additionalData['navigationAttributes']);
    }

    /**
     * @throws \JsonException
     */
    public function testReverseWithShowNavAttributesNotArray(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632156, 2.3887722)
                                    ->withData('showNavAttributes', 'access');

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Show map nav attribute param(s) must be an array');

        $provider->reverseQuery($reverseQuery);
    }

    /**
     * @throws \JsonException
     */
    public function testReverseWithInvalidShowNavAttributes(): void
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        $provider = Here::createUsingApiKey($this->getHttpClient($_SERVER['HERE_API_KEY']), $_SERVER['HERE_API_KEY'], true);

        $reverseQuery = ReverseQuery::fromCoordinates(48.8632156, 2.3887722)
                                    ->withData('showNavAttributes', ['notAParam']);

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Show map nav attribute param(s) "notAParam" are invalid');

        $provider->reverseQuery($reverseQuery);
    }

    public function testGetName(): void
    {
        $provider = new Here($this->getMockedHttpClient(), 'appId', 'appCode');
        $this->assertEquals('Here', $provider->getName());
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeWithInvalidData(): void
    {
        $this->expectException(InvalidServerResponse::class);

        $provider = new Here($this->getMockedHttpClient(), 'appId', 'appCode');
        $provider->geocodeQuery(GeocodeQuery::create('foobar'));
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeIpv4(): void
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The Here provider does not support IP addresses, only street addresses.');

        $provider = $this->getProvider();
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The Here provider does not support IP addresses, only street addresses.');

        $provider = $this->getProvider();
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeInvalidApiKey(): void
    {
        $this->expectException(InvalidCredentials::class);
        $this->expectExceptionMessage('Invalid or missing api key.');

        $provider = new Here(
            $this->getMockedHttpClient(
                '{
                    "error": "Unauthorized",
                    "error_description": "apiKey invalid. apiKey not found."
                }'
            ),
            'appId',
            'appCode'
        );
        $provider->geocodeQuery(GeocodeQuery::create('New York'));
    }

    /**
     * @throws \JsonException
     */
    public function testGeocodeWithRealIPv6(): void
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The Here provider does not support IP addresses, only street addresses.');

        $provider = $this->getProvider();
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }

    public function getProvider(): Here
    {
        if (!isset($_SERVER['HERE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the HERE_API_KEY value in phpunit.xml');
        }

        return Here::createUsingApiKey($this->getHttpClient(), $_SERVER['HERE_API_KEY'], true);
    }
}
