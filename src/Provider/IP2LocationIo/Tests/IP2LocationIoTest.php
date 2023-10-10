<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IP2LocationIo\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Provider\IP2LocationIo\IP2LocationIo;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class IP2LocationIoTest extends BaseTestCase
{
	protected function getCacheDir(): string
	{
		return __DIR__ . '/.cached_responses';
	}

	public function testGetName(): void
	{
		$provider = new IP2LocationIo($this->getMockedHttpClient(), 'api_key');
		$this->assertEquals('ip2location_io', $provider->getName());
	}

	public function testGeocodeWithRandomString(): void
	{
		$this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
		$this->expectExceptionMessage('The IP2LocationIo provider does not support street addresses, only IPv4 or IPv6 addresses.');

		$provider = new IP2LocationIo($this->getMockedHttpClient(), 'api_key');
		$provider->geocodeQuery(GeocodeQuery::create('foobar'));
	}

	public function testGeocodeWithAddress(): void
	{
		$this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
		$this->expectExceptionMessage('The IP2LocationIo provider does not support street addresses, only IPv4 or IPv6 addresses.');

		$provider = new IP2LocationIo($this->getMockedHttpClient(), 'api_key');
		$provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
	}

	public function testGeocodeWithLocalhostIPv4(): void
	{
		$provider = new IP2LocationIo($this->getMockedHttpClient(), 'api_key');
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

	public function testGeocodeWithRealIPv4GetsNullContent(): void
	{
		$this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

		$provider = new IP2LocationIo($this->getMockedHttpClient(), 'api_key');
		$provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));
	}

	public function testGeocodeWithRealIPv4GetsEmptyContent(): void
	{
		$this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

		$provider = new IP2LocationIo($this->getMockedHttpClient(), 'api_key');
		$provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));
	}

	public function testGeocodeWithRealIPv4(): void
	{
		if (!isset($_SERVER['IP2LOCATION_IO_API_KEY'])) {
			$this->markTestSkipped('You need to configure the IP2LOCATION_IO_API_KEY value in phpunit.xml');
		}

		$provider = new IP2LocationIo($this->getHttpClient($_SERVER['IP2LOCATION_IO_API_KEY']), $_SERVER['IP2LOCATION_IO_API_KEY']);
		$results = $provider->geocodeQuery(GeocodeQuery::create('8.8.8.8'));

		$this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
		$this->assertCount(1, $results);

		/** @var Location $result */
		$result = $results->first();
		$this->assertInstanceOf('\Geocoder\Model\Address', $result);
		$this->assertEqualsWithDelta(37.405992, $result->getCoordinates()->getLatitude(), 0.001);
		$this->assertEqualsWithDelta(-122.079, $result->getCoordinates()->getLongitude(), 0.001);
		$this->assertEquals(94043, $result->getPostalCode());
		$this->assertEquals('Mountain View', $result->getLocality());
		$this->assertCount(1, $result->getAdminLevels());
		$this->assertEquals('California', $result->getAdminLevels()->get(1)->getName());
		$this->assertEquals('United States of America', $result->getCountry()->getName());
		$this->assertEquals('US', $result->getCountry()->getCode());
		$this->assertEquals('America/Los_Angeles', $result->getTimezone());
	}

	public function testGeocodeWithRealIPv6(): void
	{
		if (!isset($_SERVER['IP2LOCATION_IO_API_KEY'])) {
			$this->markTestSkipped('You need to configure the IP2LOCATION_IO_API_KEY value in phpunit.xml');
		}

		$provider = new IP2LocationIo($this->getHttpClient($_SERVER['IP2LOCATION_IO_API_KEY']), $_SERVER['IP2LOCATION_IO_API_KEY']);
		$results = $provider->geocodeQuery(GeocodeQuery::create('2001:4860:4860::8888'));

		$this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
		$this->assertCount(1, $results);

		/** @var Location $result */
		$result = $results->first();
		$this->assertInstanceOf('\Geocoder\Model\Address', $result);
		$this->assertEqualsWithDelta(37.38605, $result->getCoordinates()->getLatitude(), 0.001);
		$this->assertEqualsWithDelta(-122.08385, $result->getCoordinates()->getLongitude(), 0.001);
		$this->assertEquals(94041, $result->getPostalCode());
		$this->assertEquals('Mountain View', $result->getLocality());
		$this->assertCount(1, $result->getAdminLevels());
		$this->assertEquals('California', $result->getAdminLevels()->get(1)->getName());
		$this->assertEquals('United States of America', $result->getCountry()->getName());
		$this->assertEquals('US', $result->getCountry()->getCode());
		$this->assertEquals('America/Los_Angeles', $result->getTimezone());
	}

	public function testReverse(): void
	{
		$this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
		$this->expectExceptionMessage('The IP2LocationIo provider is not able to do reverse geocoding.');

		$provider = new IP2LocationIo($this->getMockedHttpClient(), 'api_key');
		$provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
	}
}
