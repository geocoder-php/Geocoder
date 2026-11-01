<?php

declare(strict_types=1);

/*
 * This file is part of the OpenGeocoding provider for Geocoder PHP.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\OpenGeocoding\Tests;

use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Provider\OpenGeocoding\Model\OpenGeocodingAddress;
use Geocoder\Provider\OpenGeocoding\OpenGeocoding;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Nyholm\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class OpenGeocodingTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    private function apiKey(): string
    {
        return (string) getenv('OPENGEOCODING_API_KEY');
    }

    public function testGetName(): void
    {
        $provider = new OpenGeocoding($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('opengeocoding', $provider->getName());
    }

    public function testEmptyApiKey(): void
    {
        $this->expectException(InvalidCredentials::class);
        new OpenGeocoding($this->getMockedHttpClient(), '');
    }

    public function testEmptySearchResponseIsAnEmptyCollection(): void
    {
        $provider = new OpenGeocoding($this->getMockedHttpClient('{}'), 'api_key');
        $this->assertCount(0, $provider->geocodeQuery(GeocodeQuery::create('foobar')));
    }

    public function testEmptyReverseResponseIsAnEmptyCollection(): void
    {
        $provider = new OpenGeocoding($this->getMockedHttpClient('{}'), 'api_key');
        $this->assertCount(0, $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2)));
    }

    public function testSearchRequest(): void
    {
        $client = $this->getMockedHttpClientCallback(function (RequestInterface $request) {
            $this->assertSame('GET', $request->getMethod());
            $this->assertSame('https://api.opengeocoding.com/v1/search?addressString=10+Rue+de+Rivoli%2C+Paris', (string) $request->getUri());
            $this->assertSame('secret_key', $request->getHeaderLine('x-api-key'));

            return '{}';
        });

        $provider = new OpenGeocoding($client, 'secret_key');
        $provider->geocodeQuery(GeocodeQuery::create('10 Rue de Rivoli, Paris')->withLimit(3)->withLocale('fr'));
    }

    public function testReverseRequestCapsSizeAndPassesRadius(): void
    {
        $client = $this->getMockedHttpClientCallback(function (RequestInterface $request) {
            $this->assertSame('https://api.opengeocoding.com/v1/reverse?lat=55.676&lon=12.568&size=10&radiusM=200', (string) $request->getUri());
            $this->assertSame('secret_key', $request->getHeaderLine('x-api-key'));

            return '{"results":[]}';
        });

        $provider = new OpenGeocoding($client, 'secret_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(55.676, 12.568)->withLimit(50)->withData('radiusM', 200));
    }

    public function testAdminLevelsFallBackToStateAndCounty(): void
    {
        $body = '{"displayName":"Somewhere","geoCoordinates":{"lat":1.5,"lon":2.5},"addressDetails":{"state":"A State","county":"A County","town":"A Town","countryCode":"xx"}}';
        $provider = new OpenGeocoding($this->getMockedHttpClient($body), 'api_key');

        /** @var OpenGeocodingAddress $result */
        $result = $provider->geocodeQuery(GeocodeQuery::create('somewhere'))->first();
        $this->assertNull($result->getBounds());
        $this->assertEquals('A Town', $result->getLocality());
        $this->assertEquals('XX', $result->getCountry()->getCode());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('A State', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('A County', $result->getAdminLevels()->get(2)->getName());
        $this->assertFalse($result->isGoodMatch());
    }

    public function testIpAddressesAreUnsupported(): void
    {
        $this->expectException(UnsupportedOperation::class);
        $provider = new OpenGeocoding($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @return array<string, array{int, string, class-string<\Throwable>, string|null}>
     */
    public function errorProvider(): array
    {
        return [
            '401' => [401, '{"code":"unauthenticated","message":"The API key is invalid, expired or revoked."}', InvalidCredentials::class, 'The API key is invalid, expired or revoked.'],
            '403' => [403, '{"code":"permission_denied","message":"Origin not allowed."}', InvalidCredentials::class, 'Origin not allowed.'],
            '429' => [429, '{"code":"resource_exhausted","message":"Too many requests per second for your plan."}', QuotaExceeded::class, 'Too many requests per second for your plan.'],
            '400' => [400, '{"code":"invalid_argument","message":"addressString cannot be blank"}', InvalidArgument::class, 'addressString cannot be blank'],
            '400 without API error' => [400, '', InvalidServerResponse::class, null],
            '404' => [404, '{"code":"not_found","message":"Not found."}', InvalidServerResponse::class, null],
            '503' => [503, '{"code":"unavailable","message":"Unavailable."}', InvalidServerResponse::class, null],
            '200 not JSON' => [200, '<html>', InvalidServerResponse::class, null],
        ];
    }

    /**
     * @dataProvider errorProvider
     *
     * @param class-string<\Throwable> $exception
     */
    public function testErrors(int $status, string $body, string $exception, ?string $message): void
    {
        $client = $this->getMockedHttpClientCallback(fn () => new Response($status, [], $body));
        $provider = new OpenGeocoding($client, 'api_key');

        $this->expectException($exception);
        if (null !== $message) {
            $this->expectExceptionMessage($message);
        }
        $provider->geocodeQuery(GeocodeQuery::create('foo'));
    }

    public function testGeocodeWithRealAddress(): void
    {
        $provider = new OpenGeocoding($this->getHttpClient($this->apiKey()), $this->apiKey());
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 Rue de Rivoli, Paris')->withLimit(5));

        $this->assertCount(1, $results);

        /** @var OpenGeocodingAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(OpenGeocodingAddress::class, $result);
        $this->assertEqualsWithDelta(48.8555, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(2.3604, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertNotNull($result->getBounds());
        $this->assertLessThan($result->getBounds()->getNorth(), $result->getBounds()->getSouth());
        $this->assertLessThan($result->getBounds()->getEast(), $result->getBounds()->getWest());
        $this->assertEquals('10', $result->getStreetNumber());
        $this->assertEquals('Rue de Rivoli', $result->getStreetName());
        $this->assertEquals('75004', $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Ile-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('IDF', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
        $this->assertEquals('10, Rue de Rivoli, Paris, Ile-de-France, France', $result->getDisplayName());
        $this->assertTrue($result->isGoodMatch());
        $this->assertNull($result->getDistance());
    }

    public function testReverseWithRealCoordinates(): void
    {
        $provider = new OpenGeocoding($this->getHttpClient($this->apiKey()), $this->apiKey());
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(55.676, 12.568)->withLimit(2));

        $this->assertCount(2, $results);

        /** @var OpenGeocodingAddress $result */
        $result = $results->first();
        $this->assertEqualsWithDelta(55.676, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(12.568, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertEquals('Rådhuspladsen', $result->getStreetName());
        $this->assertEquals('1550', $result->getPostalCode());
        $this->assertEquals('Copenhagen', $result->getLocality());
        $this->assertEquals('Capital Region of Denmark', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('DK', $result->getCountry()->getCode());
        $this->assertEquals('address', $result->getLayer());
        $this->assertNotNull($result->getDistance());
        $this->assertLessThan(100, $result->getDistance());
    }
}
