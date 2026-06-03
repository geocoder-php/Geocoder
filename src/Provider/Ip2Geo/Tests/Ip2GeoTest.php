<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Ip2Geo\Tests;

use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Provider\Ip2Geo\Ip2Geo;
use Geocoder\Provider\Ip2Geo\Ip2GeoAddress;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class Ip2GeoTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new Ip2Geo($this->getMockedHttpClient(), 'api-key');

        $this->assertSame('ip2geo', $provider->getName());
    }

    public function testEmptyApiKeyThrows(): void
    {
        $this->expectException(InvalidCredentials::class);
        $this->expectExceptionMessage('An API key is required.');

        new Ip2Geo($this->getMockedHttpClient(), '');
    }

    public function testGeocodeWithStreetAddress(): void
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The ip2geo provider does not support street addresses, only IP addresses.');

        $provider = new Ip2Geo($this->getMockedHttpClient(), 'api-key');
        $provider->geocodeQuery(GeocodeQuery::create('123 Main Street'));
    }

    public function testReverseQuery(): void
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The ip2geo provider is not able to do reverse geocoding.');

        $provider = new Ip2Geo($this->getMockedHttpClient(), 'api-key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(37.386, -122.0838));
    }

    public function testGeocodeWithLocalhostIpv4(): void
    {
        $provider = new Ip2Geo($this->getMockedHttpClient(), 'api-key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        $result = $results->first();
        $this->assertNull($result->getLocality());
        $this->assertNull($result->getCoordinates());
    }

    public function testGeocodeWithLocalhostIpv6(): void
    {
        $provider = new Ip2Geo($this->getMockedHttpClient(), 'api-key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('::1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);
    }

    public function testGeocodeWithInvalidResponse(): void
    {
        $this->expectException(InvalidServerResponse::class);

        $provider = new Ip2Geo($this->getMockedHttpClient('not-json'), 'api-key');
        $provider->geocodeQuery(GeocodeQuery::create('8.8.8.8'));
    }

    public function testGeocodeWithUnsuccessfulResponse(): void
    {
        $body = json_encode([
            'success' => false,
            'message' => 'Invalid API key',
        ]);

        $provider = new Ip2Geo($this->getMockedHttpClient($body), 'invalid-key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('8.8.8.8'));

        $this->assertEmpty($results);
    }

    public function testGeocodeWithRealIp(): void
    {
        $body = json_encode($this->getSuccessResponse());

        $provider = new Ip2Geo($this->getMockedHttpClient($body), 'api-key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('8.8.8.8'));

        $this->assertCount(1, $results);

        /** @var Ip2GeoAddress $result */
        $result = $results->first();

        $this->assertInstanceOf(Ip2GeoAddress::class, $result);

        // Standard geocoder fields
        $this->assertSame('ip2geo', $result->getProvidedBy());
        $this->assertSame('Mountain View', $result->getLocality());
        $this->assertSame('94035', $result->getPostalCode());
        $this->assertEqualsWithDelta(37.386, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-122.0838, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertSame('United States', $result->getCountry()->getName());
        $this->assertSame('US', $result->getCountry()->getCode());
        $this->assertSame('California', $result->getAdminLevels()->get(1)->getName());
        $this->assertSame('CA', $result->getAdminLevels()->get(1)->getCode());
        $this->assertSame('America/Los_Angeles', $result->getTimezone());

        // Extra ip2geo fields
        $this->assertSame('8.8.8.8', $result->getIp());
        $this->assertSame('IPv4', $result->getIpType());
        $this->assertFalse($result->isEu());
        $this->assertSame('North America', $result->getContinentName());
        $this->assertSame('NA', $result->getContinentCode());
        $this->assertSame('+1', $result->getPhoneCode());
        $this->assertSame('Washington D.C.', $result->getCapital());
        $this->assertSame('.us', $result->getTld());
        $this->assertSame('https://flagcdn.com/us.svg', $result->getFlagImg());
        $this->assertSame('USD', $result->getCurrencyCode());
        $this->assertSame('United States Dollar', $result->getCurrencyName());
        $this->assertSame('$', $result->getCurrencySymbol());
        $this->assertSame(5375480, $result->getGeonameId());
        $this->assertSame(6255149, $result->getContinentGeonameId());
        $this->assertSame(6252001, $result->getCountryGeonameId());
        $this->assertSame(807, $result->getMetroCode());
        $this->assertSame('U+1F1FA U+1F1F8', $result->getFlagEmojiUnicode());
        $this->assertSame(6252001, $result->getRegisteredCountryGeonameId());
        $this->assertSame(1000, $result->getAccuracyRadius());
        $this->assertSame('2026-04-05T10:30:00-07:00', $result->getTimeNow());
        $this->assertSame(15169, $result->getAsnNumber());
        $this->assertSame('Google LLC', $result->getAsnName());
        $this->assertSame('United States', $result->getRegisteredCountryName());
        $this->assertSame('US', $result->getRegisteredCountryCode());
    }

    public function testGeocodeWithMinimalData(): void
    {
        $body = json_encode([
            'success' => true,
            'data' => [
                'ip' => '1.1.1.1',
                'type' => 'IPv4',
                'continent' => [
                    'name' => 'Oceania',
                    'code' => 'OC',
                    'country' => [
                        'name' => 'Australia',
                        'code' => 'AU',
                    ],
                ],
            ],
        ]);

        $provider = new Ip2Geo($this->getMockedHttpClient($body), 'api-key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('1.1.1.1'));

        /** @var Ip2GeoAddress $result */
        $result = $results->first();

        $this->assertInstanceOf(Ip2GeoAddress::class, $result);
        $this->assertSame('1.1.1.1', $result->getIp());
        $this->assertSame('Australia', $result->getCountry()->getName());
        $this->assertSame('AU', $result->getCountry()->getCode());
        $this->assertNull($result->getLocality());
        $this->assertNull($result->getCoordinates());
        $this->assertNull($result->getAsnNumber());
        $this->assertNull($result->getCurrencyCode());
        $this->assertNull($result->getFlagEmoji());
    }

    /**
     * Returns a full successful API response for testing.
     */
    private function getSuccessResponse(): array
    {
        return [
            'success' => true,
            'data' => [
                'ip' => '8.8.8.8',
                'type' => 'IPv4',
                'is_eu' => false,
                'continent' => [
                    'name' => 'North America',
                    'code' => 'NA',
                    'geoname_id' => 6255149,
                    'country' => [
                        'name' => 'United States',
                        'code' => 'US',
                        'geoname_id' => 6252001,
                        'phone_code' => '+1',
                        'capital' => 'Washington D.C.',
                        'tld' => '.us',
                        'flag' => [
                            'emoji' => "\u{1F1FA}\u{1F1F8}",
                            'emoji_unicode' => 'U+1F1FA U+1F1F8',
                            'img' => 'https://flagcdn.com/us.svg',
                        ],
                        'currency' => [
                            'name' => 'United States Dollar',
                            'code' => 'USD',
                            'symbol' => '$',
                        ],
                        'subdivision' => [
                            'name' => 'California',
                            'code' => 'CA',
                        ],
                        'city' => [
                            'name' => 'Mountain View',
                            'latitude' => 37.386,
                            'longitude' => -122.0838,
                            'postal_code' => '94035',
                            'geoname_id' => 5375480,
                            'metro_code' => 807,
                            'accuracy_radius' => 1000,
                            'timezone' => [
                                'name' => 'America/Los_Angeles',
                                'time_now' => '2026-04-05T10:30:00-07:00',
                            ],
                        ],
                    ],
                ],
                'asn' => [
                    'number' => 15169,
                    'name' => 'Google LLC',
                ],
                'registered_country' => [
                    'name' => 'United States',
                    'code' => 'US',
                    'geoname_id' => 6252001,
                ],
            ],
        ];
    }
}
