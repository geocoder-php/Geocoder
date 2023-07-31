<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Geonames\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Provider\Geonames\Geonames;
use Geocoder\Provider\Geonames\Model\GeonamesAddress;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class GeonamesTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new Geonames($this->getMockedHttpClient(), 'username');
        $this->assertEquals('geonames', $provider->getName());
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Geonames provider does not support IP addresses.');

        $provider = new Geonames($this->getMockedHttpClient(), 'username');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Geonames provider does not support IP addresses.');

        $provider = new Geonames($this->getMockedHttpClient(), 'username');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithUnknownCity(): void
    {
        $noPlacesFoundResponse = <<<'JSON'
{
    "totalResultsCount": 0,
    "geonames": [ ]
}
JSON;
        $provider = new Geonames($this->getMockedHttpClient($noPlacesFoundResponse), 'username');
        $result = $provider->geocodeQuery(GeocodeQuery::create('BlaBlaBla'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGeocodeWithRealPlace(): void
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new Geonames($this->getHttpClient($_SERVER['GEONAMES_USERNAME']), $_SERVER['GEONAMES_USERNAME']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Harrods, London'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);

        /** @var GeonamesAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(51.49957, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-0.16359, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountry()->getCode());
        $this->assertEquals('Europe/London', $result->getTimezone());

        $this->assertEquals('MALL', $result->getFcode());
        $this->assertEquals('spot, building, farm', $result->getFclName());
        $this->assertEquals('Harrods', $result->getName());
        $this->assertEquals('Harrods', $result->getAsciiName());
        $this->assertEquals(0, $result->getPopulation());
        $this->assertEquals(6944333, $result->getGeonameId());
        $this->assertCount(10, $result->getAlternateNames());
    }

    public function testGeocodeWithMultipleRealPlaces(): void
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new Geonames($this->getHttpClient($_SERVER['GEONAMES_USERNAME']), $_SERVER['GEONAMES_USERNAME']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('London'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(51.508528775863, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-0.12574195861816, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(51.151689398345, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(-0.70360885396019, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(51.865368153381, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(0.45212493672386, $result->getBounds()->getEast(), 0.01);
        $this->assertEquals('London', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Greater London', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('England', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountry()->getCode());
        $this->assertEquals('Europe/London', $result->getTimezone());

        /** @var Location $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(-33.015285093464, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(27.911624908447, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(-33.104996458003, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(27.804746435655, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(-32.925573728925, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(28.018503381239, $result->getBounds()->getEast(), 0.01);
        $this->assertEquals('East London', $result->getLocality());
        $this->assertCount(3, $result->getAdminLevels());
        $this->assertEquals('Buffalo City', $result->getAdminLevels()->get(3)->getName());
        $this->assertEquals('Buffalo City Metropolitan Municipality', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Eastern Cape', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('South Africa', $result->getCountry()->getName());
        $this->assertEquals('ZA', $result->getCountry()->getCode());
        $this->assertEquals('Africa/Johannesburg', $result->getTimezone());

        /** @var Location $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(51.512788890295, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-0.091838836669922, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(51.155949512764, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(-0.66976046752962, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(51.869628267826, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(0.48608279418978, $result->getBounds()->getEast(), 0.01);
        $this->assertEquals('City of London', $result->getLocality());
        $this->assertCount(3, $result->getAdminLevels());
        $this->assertEquals('City of London', $result->getAdminLevels()->get(3)->getName());
        $this->assertEquals('Greater London', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('England', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountry()->getCode());
        $this->assertEquals('Europe/London', $result->getTimezone());

        /** @var Location $result */
        $result = $results->get(3);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(42.983389283, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-81.233042387, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(42.907075642763, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(-81.337489676463, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(43.059702923237, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(-81.128595097537, $result->getBounds()->getEast(), 0.01);
        $this->assertEquals('London', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Ontario', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Canada', $result->getCountry()->getName());
        $this->assertEquals('CA', $result->getCountry()->getCode());
        $this->assertEquals('America/Toronto', $result->getTimezone());

        /** @var Location $result */
        $result = $results->get(4);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(41.3556539, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-72.0995209, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(41.334087887904, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(-72.128261254846, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(41.377219912096, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(-72.070780545154, $result->getBounds()->getEast(), 0.01);
        $this->assertEquals('New London', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('New London County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Connecticut', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('America/New_York', $result->getTimezone());
    }

    public function testGeocodeWithMultipleRealPlacesWithLocale(): void
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new Geonames($this->getHttpClient($_SERVER['GEONAMES_USERNAME']), $_SERVER['GEONAMES_USERNAME']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('London')->withLocale('it_IT'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(51.50853, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-0.12574, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(51.15169, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(-0.70361, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(51.86537, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(0.45212, $result->getBounds()->getEast(), 0.01);
        $this->assertEquals('Londra', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Greater London', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Inghilterra', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Regno Unito', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountry()->getCode());
        $this->assertEquals('Europe/London', $result->getTimezone());

        /** @var Location $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(-33.015285093464, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(27.911624908447, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(-33.104996458003, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(27.804746435655, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(-32.925573728925, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(28.018503381239, $result->getBounds()->getEast(), 0.01);
        $this->assertEquals('East London', $result->getLocality());
        $this->assertCount(3, $result->getAdminLevels());
        $this->assertEquals('Buffalo City', $result->getAdminLevels()->get(3)->getName());
        $this->assertEquals('Buffalo City Metropolitan Municipality', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Eastern Cape', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Sudafrica', $result->getCountry()->getName());
        $this->assertEquals('ZA', $result->getCountry()->getCode());
        $this->assertEquals('Africa/Johannesburg', $result->getTimezone());

        /** @var Location $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(51.512788890295, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-0.091838836669922, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(51.155949512764, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(-0.66976046752962, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(51.869628267826, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(0.48608279418978, $result->getBounds()->getEast(), 0.01);
        $this->assertEquals('Città di Londra', $result->getLocality());
        $this->assertCount(3, $result->getAdminLevels());
        $this->assertEquals('City of London', $result->getAdminLevels()->get(3)->getName());
        $this->assertEquals('Greater London', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Inghilterra', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Regno Unito', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountry()->getCode());
        $this->assertEquals('Europe/London', $result->getTimezone());

        /** @var Location $result */
        $result = $results->get(3);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(42.983389283, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-81.233042387, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(42.907075642763, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(-81.337489676463, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(43.059702923237, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(-81.128595097537, $result->getBounds()->getEast(), 0.01);
        $this->assertEquals('London', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Ontario', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Canada', $result->getCountry()->getName());
        $this->assertEquals('CA', $result->getCountry()->getCode());
        $this->assertEquals('America/Toronto', $result->getTimezone());

        /** @var Location $result */
        $result = $results->get(4);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(41.3556539, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-72.0995209, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(41.334087887904, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(-72.128261254846, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(41.377219912096, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(-72.070780545154, $result->getBounds()->getEast(), 0.01);
        $this->assertEquals('New London', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Contea di New London', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Connecticut', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Stati Uniti', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('America/New_York', $result->getTimezone());
    }

    public function testReverseWithRealCoordinates(): void
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new Geonames($this->getHttpClient($_SERVER['GEONAMES_USERNAME']), $_SERVER['GEONAMES_USERNAME']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(51.50853, -0.12574));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(51.50853, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-0.12574, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('London', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Greater London', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('England', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountry()->getCode());
        $this->assertEquals('Europe/London', $result->getTimezone());
    }

    public function testReverseWithRealCoordinatesWithLocale(): void
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new Geonames($this->getHttpClient($_SERVER['GEONAMES_USERNAME']), $_SERVER['GEONAMES_USERNAME']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(51.50853, -0.12574)->withLocale('it_IT'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(51.50853, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-0.12574, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Londra', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Greater London', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Inghilterra', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Regno Unito', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountry()->getCode());
        $this->assertEquals('Europe/London', $result->getTimezone());
    }

    public function testReverseWithBadCoordinates(): void
    {
        $badCoordinateResponse = <<<'JSON'
{
    "geonames": [ ]
}
JSON;
        $provider = new Geonames($this->getMockedHttpClient($badCoordinateResponse), 'username');
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(-80.000000, -170.000000));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }
}
