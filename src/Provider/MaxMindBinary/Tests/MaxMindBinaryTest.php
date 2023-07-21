<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\MaxMindBinary\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Provider\MaxMindBinary\MaxMindBinary;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class MaxMindBinaryTest extends BaseTestCase
{
    private string $binaryFile;

    public function setUp(): void
    {
        $this->binaryFile = __DIR__.'/fixtures/GeoLiteCity.dat';
    }

    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public static function setUpBeforeClass(): void
    {
        if (false == function_exists('geoip_open')) {
            self::markTestSkipped('The maxmind\'s official lib required to run these tests.');
        }

        if (false == function_exists('GeoIP_record_by_addr')) {
            self::markTestSkipped('The maxmind\'s official lib required to run these tests.');
        }
    }

    /**
     * @return array<string, string[]>
     */
    public static function provideIps(): array
    {
        return [
            '24.24.24.24' => ['24.24.24.24', 'East Syracuse', 'United States'],
            '80.24.24.24' => ['80.24.24.24', 'Sabadell', 'Spain'],
        ];
    }

    public function testThrowIfNotExistBinaryFileGiven(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidArgument::class);
        $this->expectExceptionMessage('Given MaxMind dat file "not_exist.dat" does not exist.');

        new MaxMindBinary('not_exist.dat');
    }

    public function testLocationResultContainsExpectedFieldsForAnAmericanIp(): void
    {
        $provider = new MaxMindBinary($this->binaryFile);
        $results = $provider->geocodeQuery(GeocodeQuery::create('24.24.24.24'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);

        $this->assertEqualsWithDelta(43.089200000000005, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-76.025000000000006, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals('13057', $result->getPostalCode());
        $this->assertEquals('East Syracuse', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('NY', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testLocationResultContainsExpectedFieldsForASpanishIp(): void
    {
        $provider = new MaxMindBinary($this->binaryFile);
        $results = $provider->geocodeQuery(GeocodeQuery::create('80.24.24.24'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);

        $this->assertEqualsWithDelta(41.543299999999988, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(2.1093999999999937, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Sabadell', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('56', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Spain', $result->getCountry()->getName());
        $this->assertEquals('ES', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    /**
     * @dataProvider provideIps
     */
    public function testFindLocationByIp(string $ip, ?string $expectedCity, ?string $expectedCountry): void
    {
        $provider = new MaxMindBinary($this->binaryFile);
        $results = $provider->geocodeQuery(GeocodeQuery::create($ip));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals($expectedCity, $result->getLocality());
        $this->assertEquals($expectedCountry, $result->getCountry()->getName());
    }

    public function testShouldReturnResultsAsUtf8Encoded(): void
    {
        $provider = new MaxMindBinary($this->binaryFile);
        $results = $provider->geocodeQuery(GeocodeQuery::create('212.51.181.237'));

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertSame('Châlette-sur-loing', $result->getLocality());
    }

    public function testGetName(): void
    {
        $provider = new MaxMindBinary($this->binaryFile);

        $this->assertEquals('maxmind_binary', $provider->getName());
    }

    public function testThrowIfIpAddressCouldNotBeLocated(): void
    {
        $provider = new MaxMindBinary($this->binaryFile);
        $result = $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testThrowIfIpAddressIsNotIpV4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The MaxMindBinary provider does not support IPv6 addresses.');

        $provider = new MaxMindBinary($this->binaryFile);

        $provider->geocodeQuery(GeocodeQuery::create('2002:5018:1818:0:0:0:0:0'));
    }

    public function testThrowIfInvalidIpAddressGiven(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The MaxMindBinary provider does not support street addresses.');

        $provider = new MaxMindBinary($this->binaryFile);

        $provider->geocodeQuery(GeocodeQuery::create('invalidIp'));
    }

    public function testThrowOnReverseMethodUsage(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The MaxMindBinary is not able to do reverse geocoding.');

        $provider = new MaxMindBinary($this->binaryFile);

        $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
    }
}
