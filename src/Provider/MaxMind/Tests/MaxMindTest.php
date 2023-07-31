<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\MaxMind\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Provider\MaxMind\MaxMind;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class MaxMindTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new MaxMind($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('maxmind', $provider->getName());
    }

    public function testGeocodeWithAddress(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The MaxMind provider does not support street addresses, only IP addresses.');

        $provider = new MaxMind($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $provider = new MaxMind($this->getMockedHttpClient(), 'api_key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $provider = new MaxMind($this->getMockedHttpClient(), 'api_key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('::1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    public function testGeocodeWithRealIPv4AndNotSupportedService(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('Unknown MaxMind service foo');

        $provider = new MaxMind($this->getMockedHttpClient(), 'api_key', 'foo');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithRealIPv6AndNotSupportedService(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('Unknown MaxMind service 12345');

        $provider = new MaxMind($this->getMockedHttpClient(), 'api_key', '12345');
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));
    }

    public function testGeocodeWithRealIPv4GetsFakeContentFormattedEmpty(): void
    {
        $provider = new MaxMind($this->getMockedHttpClient(',,,,,,,,,'), 'api_key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNull($result->getCoordinates());

        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertEmpty($result->getAdminLevels());
        $this->assertNull($result->getCountry());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealIPv4GetsFakeContent(): void
    {
        $provider = new MaxMind($this->getMockedHttpClient(
            'US,TX,Plano,75093,33.034698486328,-96.813400268555,,,,'
        ), 'api_key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(33.034698486328, $result->getCoordinates()->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(-96.813400268555, $result->getCoordinates()->getLongitude(), 0.0001);
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals(75093, $result->getPostalCode());
        $this->assertEquals('Plano', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('TX', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());

        $provider2 = new MaxMind($this->getMockedHttpClient('FR,,,,,,,,,'), 'api_key');
        $result2 = $provider2->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
        $this->assertEquals('France', $result2->first()->getCountry()->getName());

        $provider3 = new MaxMind($this->getMockedHttpClient('GB,,,,,,,,,'), 'api_key');
        $result3 = $provider3->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
        $this->assertEquals('United Kingdom', $result3->first()->getCountry()->getName());

        $provider4 = new MaxMind($this->getMockedHttpClient(
            'US,CA,San Francisco,94110,37.748402,-122.415604,807,415,"Layered Technologies","Automattic"'
        ), 'api_key');
        $results = $provider4->geocodeQuery(GeocodeQuery::create('74.200.247.59'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(37.748402, $result->getCoordinates()->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(-122.415604, $result->getCoordinates()->getLongitude(), 0.0001);
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals(94110, $result->getPostalCode());
        $this->assertEquals('San Francisco', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('CA', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealIPv4AndInvalidApiKeyGetsFakeContent(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('API Key provided is not valid.');

        $provider = new MaxMind($this->getMockedHttpClient(',,,,,,,,,,INVALID_LICENSE_KEY'), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeOmniServiceWithRealIPv6AndInvalidApiKeyGetsFakeContent(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('API Key provided is not valid.');

        $provider = new MaxMind(
            $this->getMockedHttpClient(',,,,,,,,,,,,,,,,,,,,,,,,INVALID_LICENSE_KEY'),
            'api_key',
            MaxMind::OMNI_SERVICE
        );
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));
    }

    public function testGeocodeWithRealIPv4AndInvalidApiKey(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('API Key provided is not valid.');

        $provider = new MaxMind($this->getHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithRealIPv4AndInvalidApiKeyGetsFakeContent2(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('API Key provided is not valid.');

        $provider = new MaxMind($this->getMockedHttpClient(',,,,,,,,,,LICENSE_REQUIRED'), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeOmniServiceWithRealIPv6AndInvalidApiKeyGetsFakeContent2(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('API Key provided is not valid.');

        $provider = new MaxMind(
            $this->getMockedHttpClient(',,,,,,,,,,,,,,,,,,,,,,,INVALID_LICENSE_KEY'),
            'api_key',
            MaxMind::OMNI_SERVICE
        );
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));
    }

    public function testGeocodeWithRealIPv4GetsFakeContentWithIpNotFound(): void
    {
        $provider = new MaxMind($this->getMockedHttpClient(',,,,,,,,,,IP_NOT_FOUND'), 'api_key');
        $result = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGeocodeOmniServiceWithRealIPv6GetsFakeContentWithIpNotFound(): void
    {
        $provider = new MaxMind(
            $this->getMockedHttpClient(',,,,,,,,,,,,,,,,,,,,,,,IP_NOT_FOUND'),
            'api_key',
            MaxMind::OMNI_SERVICE
        );
        $result = $provider->geocodeQuery(GeocodeQuery::create('::fff:74.200.247.59'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGeocodeGetsFakeContentWithInvalidData(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

        $provider = new MaxMind($this->getMockedHttpClient(',,,,,,,,,,'), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeServiceWithRealIPv4(): void
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMind($this->getHttpClient($_SERVER['MAXMIND_API_KEY']), $_SERVER['MAXMIND_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.159'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(33.034698, $result->getCoordinates()->getLatitude(), 0.1);
        $this->assertEqualsWithDelta(-96.813400, $result->getCoordinates()->getLongitude(), 0.1);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals(75093, $result->getPostalCode());
        $this->assertEquals('Plano', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('TX', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeOmniServiceWithRealIPv4(): void
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMind(
            $this->getHttpClient($_SERVER['MAXMIND_API_KEY']),
            $_SERVER['MAXMIND_API_KEY'],
            MaxMind::OMNI_SERVICE
        );
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.159'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(33.0347, $result->getCoordinates()->getLatitude(), 0.1);
        $this->assertEqualsWithDelta(-96.8134, $result->getCoordinates()->getLongitude(), 0.1);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals(75093, $result->getPostalCode());
        $this->assertEquals('Plano', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Texas', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('TX', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('America/Chicago', $result->getTimezone());
    }

    public function testGeocodeOmniServiceWithRealIPv4WithSslAndEncoding(): void
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMind(
            $this->getHttpClient($_SERVER['MAXMIND_API_KEY']),
            $_SERVER['MAXMIND_API_KEY'],
            MaxMind::OMNI_SERVICE
        );
        $results = $provider->geocodeQuery(GeocodeQuery::create('189.26.128.80'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(-27.5833, $result->getCoordinates()->getLatitude(), 0.1);
        $this->assertEqualsWithDelta(-48.5666, $result->getCoordinates()->getLongitude(), 0.1);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Florianópolis', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Santa Catarina', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('26', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Brazil', $result->getCountry()->getName());
        $this->assertEquals('BR', $result->getCountry()->getCode());
        $this->assertEquals('America/Sao_Paulo', $result->getTimezone());
    }

    public function testGeocodeOmniServiceWithRealIPv6WithSsl(): void
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMind(
            $this->getHttpClient($_SERVER['MAXMIND_API_KEY']),
            $_SERVER['MAXMIND_API_KEY'],
            MaxMind::OMNI_SERVICE
        );
        $results = $provider->geocodeQuery(GeocodeQuery::create('2002:4293:f4d6:0:0:0:0:0'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(40.2181, $result->getCoordinates()->getLatitude(), 0.1);
        $this->assertEqualsWithDelta(-111.6133, $result->getCoordinates()->getLongitude(), 0.1);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals(84606, $result->getPostalCode());
        $this->assertEquals('Provo', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Utah', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('UT', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('America/Denver', $result->getTimezone());
    }

    public function testReverse(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The MaxMind provider is not able to do reverse geocoding.');

        $provider = new MaxMind($this->getMockedHttpClient(), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }
}
