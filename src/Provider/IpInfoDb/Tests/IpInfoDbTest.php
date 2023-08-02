<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IpInfoDb\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Provider\IpInfoDb\IpInfoDb;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class IpInfoDbTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testConstructWithInvalidPrecision(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidArgument::class);
        $this->expectExceptionMessage('Invalid precision value "foo" (allowed values: "city", "country").');

        new IpInfoDb($this->getMockedHttpClient(), 'api_key', 'foo');
    }

    public function testGetName(): void
    {
        $provider = new IpInfoDb($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('ip_info_db', $provider->getName());
    }

    public function testGeocodeWithRandomString(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The IpInfoDb provider does not support street addresses, only IPv4 addresses.');

        $provider = new IpInfoDb($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('foobar'));
    }

    public function testGeocodeWithAddress(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The IpInfoDb provider does not support street addresses, only IPv4 addresses.');

        $provider = new IpInfoDb($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $provider = new IpInfoDb($this->getMockedHttpClient(), 'api_key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNull($result->getCoordinates());

        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getTimezone());
        $this->assertEmpty($result->getAdminLevels());

        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The IpInfoDb provider does not support IPv6 addresses, only IPv4 addresses.');

        $provider = new IpInfoDb($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIPv4GetsNullContent(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

        $provider = new IpInfoDb($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));
    }

    public function testGeocodeWithRealIPv4GetsEmptyContent(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

        $provider = new IpInfoDb($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));
    }

    public function testGeocodeWithRealIPv4(): void
    {
        if (!isset($_SERVER['IPINFODB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IPINFODB_API_KEY value in phpunit.xml');
        }

        $provider = new IpInfoDb($this->getHttpClient($_SERVER['IPINFODB_API_KEY']), $_SERVER['IPINFODB_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(36.154, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-95.9928, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertEquals(74101, $result->getPostalCode());
        $this->assertEquals('Tulsa', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Oklahoma', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('America/New_York', $result->getTimezone());
    }

    public function testGeocodeWithRealIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The IpInfoDb provider does not support IPv6 addresses, only IPv4 addresses.');

        if (!isset($_SERVER['IPINFODB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IPINFODB_API_KEY value in phpunit.xml');
        }

        $provider = new IpInfoDb($this->getHttpClient($_SERVER['IPINFODB_API_KEY']), $_SERVER['IPINFODB_API_KEY']);
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.125.45.100'));
    }

    /**
     * @group temp
     */
    public function testGetGeocodedDataWithCountryPrecision(): void
    {
        if (!isset($_SERVER['IPINFODB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IPINFODB_API_KEY value in phpunit.xml');
        }

        $provider = new IpInfoDb($this->getHttpClient($_SERVER['IPINFODB_API_KEY']), $_SERVER['IPINFODB_API_KEY'], 'country');
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNull($result->getCoordinates());

        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getLocality());
        $this->assertEmpty($result->getAdminLevels());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testReverse(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The IpInfoDb provider is not able to do reverse geocoding.');

        $provider = new IpInfoDb($this->getMockedHttpClient(), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
    }
}
