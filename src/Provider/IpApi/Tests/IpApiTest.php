<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IpApi\Tests;

use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\IpApi\IpApi;
use Geocoder\Provider\IpApi\Model\IpApiLocation;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class IpApiTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new IpApi($this->getMockedHttpClient());
        $this->assertEquals('ip-api', $provider->getName());
    }

    public function testInvalidApiKey(): void
    {
        $provider = new IpApi($this->getHttpClient('InVaLiDkEy'), 'InVaLiDkEy');

        $this->expectException(InvalidCredentials::class);
        $provider->geocodeQuery(GeocodeQuery::create('64.233.160.0'));
    }

    public function testGeocodeWithAddress(): void
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The ip-api provider does not support street addresses.');

        $provider = new IpApi($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('this is not an IP address'));
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $provider = new IpApi($this->getMockedHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertNotInstanceOf(IpApiLocation::class, $result);
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $provider = new IpApi($this->getMockedHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('::1'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertNotInstanceOf(IpApiLocation::class, $result);
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    /**
     * @dataProvider apiKeyProvider
     */
    public function testGeocodeWithRealIPv4(string|null $apiKey): void
    {
        $provider = new IpApi($this->getHttpClient($apiKey), $apiKey);
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertInstanceOf(IpApiLocation::class, $result);
        $this->assertEqualsWithDelta(36.154, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-95.9928, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertEquals(null, $result->getPostalCode());
        $this->assertEquals('Tulsa', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Oklahoma', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('USD', $result->getCurrency());
        $this->assertFalse($result->isProxy());
        $this->assertTrue($result->isHosting());

        // Calling code is available only for authenticated calls
        if (null !== $apiKey) {
            $this->assertEquals('1', $result->getCallingCode());
        }
    }

    /**
     * @dataProvider apiKeyProvider
     */
    public function testGeocodeWithRealIPv6(string|null $apiKey): void
    {
        $provider = new IpApi($this->getHttpClient($apiKey), $apiKey);
        $results = $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.125.45.100'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertInstanceOf(IpApiLocation::class, $result);
        $this->assertEqualsWithDelta(36.154, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-95.9928, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertEquals(null, $result->getPostalCode());
        $this->assertEquals('Tulsa', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Oklahoma', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('USD', $result->getCurrency());
        $this->assertFalse($result->isProxy());
        $this->assertTrue($result->isHosting());

        // Calling code is available only for authenticated calls
        if (null !== $apiKey) {
            $this->assertEquals('1', $result->getCallingCode());
        }
    }

    public function testReverse(): void
    {
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The ip-api provider is not able to do reverse geocoding.');

        $provider = new IpApi($this->getMockedHttpClient());
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    /**
     * @return iterable<string, array<string|null>>
     */
    public static function apiKeyProvider(): iterable
    {
        yield 'no api key' => [null];

        if (isset($_SERVER['IP_API_KEY'])) {
            yield 'with api key' => [$_SERVER['IP_API_KEY']];
        }
    }
}
