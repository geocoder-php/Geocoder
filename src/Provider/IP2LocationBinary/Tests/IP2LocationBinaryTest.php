<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IP2LocationBinary\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\IP2LocationBinary\IP2LocationBinary;

class IP2LocationBinaryTest extends BaseTestCase
{
    private $binaryFile;

    public function setUp(): void
    {
        // Download this BIN database from https://lite.ip2location.com/database/ip-country-region-city-latitude-longitude-zipcode
        $this->binaryFile = __DIR__.'/fixtures/IP2LOCATION-LITE-DB9.IPV6.BIN';
    }

    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public static function setUpBeforeClass(): void
    {
        if (false == class_exists('\\IP2Location\\Database')) {
            self::markTestSkipped('The IP2Location\'s official library required to run these tests.');
        }
    }

    public static function provideIps()
    {
        return [
            '8.8.8.8' => ['8.8.8.8', 'Mountain View', 'United States'],
            '123.123.123.123' => ['123.123.123.123', 'Beijing', 'China'],
        ];
    }

    public function testThrowIfNotExistBinaryFileGiven()
    {
        $this->expectException(\Geocoder\Exception\InvalidArgument::class);
        $this->expectExceptionMessage('Given IP2Location BIN file "NOT_EXIST.BIN" does not exist.');

        new IP2LocationBinary('NOT_EXIST.BIN');
    }

    public function testLocationResultContainsExpectedFieldsForAnAmericanIp()
    {
        $provider = new IP2LocationBinary($this->binaryFile);
        $results = $provider->geocodeQuery(GeocodeQuery::create('8.8.8.8'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);

        $this->assertEqualsWithDelta(37.405990600586, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-122.07851409912, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals('94043', $result->getPostalCode());
        $this->assertEquals('Mountain View', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('California', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testLocationResultContainsExpectedFieldsForAChinaIp()
    {
        $provider = new IP2LocationBinary($this->binaryFile);
        $results = $provider->geocodeQuery(GeocodeQuery::create('123.123.123.123'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);

        $this->assertEqualsWithDelta(39.907501220703, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(116.39723205566, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals('100006', $result->getPostalCode());
        $this->assertEquals('Beijing', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Beijing', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('China', $result->getCountry()->getName());
        $this->assertEquals('CN', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealIPv6()
    {
        $provider = new IP2LocationBinary($this->binaryFile);
        $results = $provider->geocodeQuery(GeocodeQuery::create('2001:4860:4860::8888'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);

        $this->assertEqualsWithDelta(37.386051, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-122.083847, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals('94041', $result->getPostalCode());
        $this->assertEquals('Mountain View', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('California', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    /**
     * @dataProvider provideIps
     */
    public function testFindLocationByIp($ip, $expectedCity, $expectedCountry)
    {
        $provider = new IP2LocationBinary($this->binaryFile);
        $results = $provider->geocodeQuery(GeocodeQuery::create($ip));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals($expectedCity, $result->getLocality());
        $this->assertEquals($expectedCountry, $result->getCountry()->getName());
    }

    public function testGetName()
    {
        $provider = new IP2LocationBinary($this->binaryFile);

        $this->assertEquals('ip2location_binary', $provider->getName());
    }

    public function testThrowIfInvalidIpAddressGiven()
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The IP2LocationBinary provider does not support street addresses.');

        $provider = new IP2LocationBinary($this->binaryFile);

        $provider->geocodeQuery(GeocodeQuery::create('invalidIp'));
    }

    public function testThrowOnReverseMethodUsage()
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The IP2LocationBinary is not able to do reverse geocoding.');

        $provider = new IP2LocationBinary($this->binaryFile);

        $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
    }
}
