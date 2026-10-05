<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IPGeolocation\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Provider\IPGeolocation\IPGeolocation;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class IPGeolocationTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new IPGeolocation($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('ipgeolocation', $provider->getName());
    }

    public function testMissingApiKey(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('No API key provided.');

        new IPGeolocation($this->getMockedHttpClient(), '');
    }

    public function testGeocodeWithAddress(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The IPGeolocation provider does not support street addresses, only IP addresses.');

        $provider = new IPGeolocation($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    /** @dataProvider provideLocalhostIps */
    public function testGeocodeWithLocalhost(string $localhostIp): void
    {
        $provider = new IPGeolocation($this->getMockedHttpClient(), 'api_key');
        $results = $provider->geocodeQuery(GeocodeQuery::create($localhostIp));

        $this->assertInstanceOf(\Geocoder\Model\AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    /**
     * @return iterable<string[]>
     */
    public function provideLocalhostIps(): iterable
    {
        yield ['127.0.0.1'];
        yield ['::1'];
    }

    public function testRequestUrl(): void
    {
        $provider = new IPGeolocation($this->getMockedHttpClientCallback(
            function (RequestInterface $request): ResponseInterface {
                $this->assertEquals('GET', $request->getMethod());
                $this->assertEquals(
                    'https://api.ipgeolocation.io/v3/ipgeo?apiKey=api_key&ip=165.227.0.0&fields=location%2Ctime_zone.name&lang=fr',
                    (string) $request->getUri()
                );

                return $this->getResponse(200, $this->getFreePlanBody());
            }
        ), 'api_key');

        $provider->geocodeQuery(GeocodeQuery::create('165.227.0.0')->withLocale('fr'));
    }

    public function testGeocodeWithIPv4(): void
    {
        $provider = new IPGeolocation($this->getMockedHttpClient($this->getFreePlanBody()), 'api_key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('165.227.0.0'));

        $this->assertInstanceOf(\Geocoder\Model\AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf(\Geocoder\Model\Address::class, $result);
        $this->assertEqualsWithDelta(37.35983, $result->getCoordinates()->getLatitude(), 0.00001);
        $this->assertEqualsWithDelta(-121.98144, $result->getCoordinates()->getLongitude(), 0.00001);
        $this->assertEquals('Santa Clara', $result->getLocality());
        $this->assertEquals('95051', $result->getPostalCode());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('California', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('US-CA', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Santa Clara County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('America/Los_Angeles', $result->getTimezone());
        $this->assertEquals('ipgeolocation', $result->getProvidedBy());
    }

    public function testGeocodeWithEmptyFields(): void
    {
        $body = json_encode([
            'ip' => '2001:4860:4860::8888',
            'location' => [
                'country_code2' => 'US',
                'country_name' => 'United States',
                'state_prov' => '',
                'state_code' => '',
                'district' => '',
                'city' => '',
                'zipcode' => '',
                'latitude' => '37.75100',
                'longitude' => '-97.82200',
            ],
        ]);

        $provider = new IPGeolocation($this->getMockedHttpClient($body), 'api_key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('2001:4860:4860::8888'));

        /** @var Location $result */
        $result = $results->first();
        $this->assertNull($result->getLocality());
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getTimezone());
        $this->assertCount(0, $result->getAdminLevels());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    /** @dataProvider provideNoResultStatusCodes */
    public function testGeocodeWithNoResult(int $statusCode, string $message): void
    {
        $provider = new IPGeolocation(
            $this->getMockedHttpClient(json_encode(['message' => $message]), $statusCode),
            'api_key'
        );

        $results = $provider->geocodeQuery(GeocodeQuery::create('10.0.0.1'));

        $this->assertInstanceOf(\Geocoder\Model\AddressCollection::class, $results);
        $this->assertCount(0, $results);
    }

    /**
     * @return iterable<array{int, string}>
     */
    public function provideNoResultStatusCodes(): iterable
    {
        yield [404, 'Provided IPv4 or IPv6 address does not exist in our database.'];
        yield [423, "'10.0.0.1' is a bogon IP address."];
    }

    public function testInvalidApiKey(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);

        $body = json_encode(['message' => 'Provided API key is not valid. Contact technical support for assistance at support@ipgeolocation.io']);
        $provider = new IPGeolocation($this->getMockedHttpClient($body, 401), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('165.227.0.0'));
    }

    public function testQuotaExceeded(): void
    {
        $this->expectException(\Geocoder\Exception\QuotaExceeded::class);

        $provider = new IPGeolocation($this->getMockedHttpClient('{"message":"You have exceeded the limit"}', 429), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('165.227.0.0'));
    }

    public function testReverse(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The IPGeolocation provider is not able to do reverse geocoding.');

        $provider = new IPGeolocation($this->getMockedHttpClient(), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
    }

    private function getResponse(int $statusCode, string $body): ResponseInterface
    {
        return new \Nyholm\Psr7\Response($statusCode, [], $body);
    }

    /**
     * The free plan example from the IPGeolocation.io API reference, filtered by the provider's `fields` parameter.
     */
    private function getFreePlanBody(): string
    {
        return json_encode([
            'ip' => '165.227.0.0',
            'location' => [
                'continent_code' => 'NA',
                'continent_name' => 'North America',
                'country_code2' => 'US',
                'country_code3' => 'USA',
                'country_name' => 'United States',
                'country_name_official' => 'United States of America',
                'country_capital' => 'Washington, D.C.',
                'state_prov' => 'California',
                'state_code' => 'US-CA',
                'district' => 'Santa Clara County',
                'city' => 'Santa Clara',
                'zipcode' => '95051',
                'latitude' => '37.35983',
                'longitude' => '-121.98144',
                'is_eu' => false,
                'country_flag' => 'https://ipgeolocation.io/static/flags/us_64.png',
                'geoname_id' => '5346804',
                'country_emoji' => '🇺🇸',
            ],
            'time_zone' => [
                'name' => 'America/Los_Angeles',
            ],
        ]);
    }
}
