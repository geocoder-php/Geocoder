<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\ArcGISOnlineTest\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Provider\ArcGISOnline\ArcGISOnline;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class ArcGISOnlineTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new ArcGISOnline($this->getMockedHttpClient());
        $this->assertEquals('arcgis_online', $provider->getName());
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The ArcGISOnline provider does not support IP addresses, only street addresses.');

        $provider = new ArcGISOnline($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The ArcGISOnline provider does not support IP addresses, only street addresses.');

        $provider = new ArcGISOnline($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealAddress(): void
    {
        $provider = new ArcGISOnline($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.863279997000461, $result->getCoordinates()->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(2.3890199980004354, $result->getCoordinates()->getLongitude(), 0.0001);
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('10 Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Île-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('FRA', $result->getCountry()->getCode());

        $this->assertNull($result->getBounds());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getAdminLevels()->get(2)->getCode());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getCountry()->getName());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithToken(): void
    {
        if (!isset($_SERVER['ARCGIS_TOKEN'])) {
            $this->markTestSkipped('You need to configure the ARCGIS_TOKEN value in phpunit.xml');
        }
        $provider = ArcGISOnline::token($this->getHttpClient($_SERVER['ARCGIS_TOKEN']), $_SERVER['ARCGIS_TOKEN']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('5754 WI-23, Spring Green, WI 53588, USA'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(43.093663, $result->getCoordinates()->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(-90.131796, $result->getCoordinates()->getLongitude(), 0.0001);
        $this->assertEquals(5754, $result->getStreetNumber());
        $this->assertEquals('5754 WI-23', $result->getStreetName());
        $this->assertEquals(53588, $result->getPostalCode());
        $this->assertEquals('Spring Green', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Wisconsin', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Iowa County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());

        $this->assertNull($result->getBounds());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getAdminLevels()->get(2)->getCode());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getCountry()->getName());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithInvalidAddressWithSourceCountry(): void
    {
        $provider = new ArcGISOnline($this->getHttpClient(), 'DNK');
        $result = $provider->geocodeQuery(GeocodeQuery::create('1 Chome-1-2 Oshiage, Sumida, Tokyo 131-8634, Japan'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testReverseWithRealCoordinates(): void
    {
        $provider = new ArcGISOnline($this->getHttpClient(), null);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.863279997000461, 2.3890199980004354));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.863279997000461, $result->getCoordinates()->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(2.3890199980004354, $result->getCoordinates()->getLongitude(), 0.0001);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('5 Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('FRA', $result->getCountry()->getCode());

        $this->assertNull($result->getBounds());
        $this->assertNull($result->getSubLocality());
        $this->assertEmpty($result->getAdminLevels());
        $this->assertNull($result->getCountry()->getName());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithCity(): void
    {
        $provider = new ArcGISOnline($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('Hannover'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(52.37227000000007, $result->getCoordinates()->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(9.738150000000076, $result->getCoordinates()->getLongitude(), 0.0001);
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Hannover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Niedersachsen', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('DEU', $result->getCountry()->getCode());

        $this->assertNull($result->getBounds());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getCountry()->getName());
        $this->assertNull($result->getTimezone());

        /** @var Location $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(39.391768472000479, $result->getCoordinates()->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(-77.440257128999633, $result->getCoordinates()->getLongitude(), 0.0001);
        $this->assertNull($result->getStreetName());
        $this->assertEquals('Hannover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Maryland', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(53.174198173, $result->getCoordinates()->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(8.5069383810005, $result->getCoordinates()->getLongitude(), 0.0001);
        $this->assertNull($result->getStreetName());
        $this->assertEquals('Hannöver', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Niedersachsen', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('DEU', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(3);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(47.111290000000054, $result->getCoordinates()->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(-101.42142999999999, $result->getCoordinates()->getLongitude(), 0.0001);
        $this->assertNull($result->getStreetName());
        $this->assertEquals('Hannover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('North Dakota', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(4);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(32.518790000000024, $result->getCoordinates()->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(-90.06298999999996, $result->getCoordinates()->getLongitude(), 0.0001);
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Mississippi', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Madison County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());
    }

    public function testGeocodeWithRealIPv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The ArcGISOnline provider does not support IP addresses, only street addresses.');

        $provider = new ArcGISOnline($this->getHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('88.188.221.14'));
    }

    public function testGeocodeWithRealIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The ArcGISOnline provider does not support IP addresses, only street addresses.');

        $provider = new ArcGISOnline($this->getHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }

    public function testGeocodeWithInvalidToken(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('Invalid token invalid-token');

        $provider = ArcGISOnline::token($this->getHttpClient(), 'invalid-token');
        $results = $provider->geocodeQuery(GeocodeQuery::create('1313 Disneyland Dr, Anaheim, CA 92802, USA'));
    }
}
