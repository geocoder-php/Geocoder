<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\MapQuest\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AdminLevel;
use Geocoder\Model\Bounds;
use Geocoder\Provider\MapQuest\MapQuest;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class MapQuestTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new MapQuest($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('map_quest', $provider->getName());
    }

    public function testGeocode(): void
    {
        $provider = new MapQuest($this->getMockedHttpClient('{}'), 'api_key');
        $result = $provider->geocodeQuery(GeocodeQuery::create('foobar'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGetNotRelevantData(): void
    {
        $json = '{"results":[{"locations":[{"street":"","postalCode":"","adminArea5":"","adminArea4":"","adminArea3":"","adminArea1":""}]}]}';

        $provider = new MapQuest($this->getMockedHttpClient($json), 'api_key');
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(11, 12));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGeocodeWithRealAddress(): void
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPQUEST_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuest($this->getHttpClient($_SERVER['MAPQUEST_API_KEY']), $_SERVER['MAPQUEST_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(2, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.866205, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.389089, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('10 Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Ile-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('FR', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());

        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.810071, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.435937, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('10 Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(94700, $result->getPostalCode());
        $this->assertEquals('Maisons-Alfort', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Créteil', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Ile-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('FR', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealSpecificAddress(): void
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPQUEST_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuest($this->getHttpClient($_SERVER['MAPQUEST_API_KEY']), $_SERVER['MAPQUEST_API_KEY']);

        $addressBuilder = new AddressBuilder('tests');
        $addressBuilder
            ->setStreetNumber('4868')
            ->setStreetName('Payne Rd')
            ->setLocality('Nashville')
            ->setSubLocality('Antioch')
            ->setAdminLevels([
                new AdminLevel(1, 'Tennessee', 'TN'),
            ])
            ->setPostalCode('37013')
            ->setCountry('USA')
            ->setCountryCode('US');
        $address = $addressBuilder->build();

        $query = GeocodeQuery::create('foobar');
        $query = $query->withData(MapQuest::DATA_KEY_ADDRESS, $address);
        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(36.062933, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-86.672811, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Payne Road', $result->getStreetName());
        $this->assertEquals('37013', $result->getPostalCode());
        $this->assertEquals('Nashville', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Davidson County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('TN', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('TN', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('US', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getTimezone());
    }

    public function testReverseWithRealCoordinates(): void
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPQUEST_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuest($this->getHttpClient($_SERVER['MAPQUEST_API_KEY']), $_SERVER['MAPQUEST_API_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(54.0484068, -2.7990345));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(54.0484068, $result->getCoordinates()->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(-2.7990345, $result->getCoordinates()->getLongitude(), 0.001);
        $this->assertEquals('Collegian W.M.C.', $result->getStreetName());
        $this->assertEquals('LA1 1NP', $result->getPostalCode());
        $this->assertEquals('Lancaster', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('England', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('GB', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountry()->getCode());

        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithCity(): void
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPQUEST_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuest($this->getHttpClient($_SERVER['MAPQUEST_API_KEY']), $_SERVER['MAPQUEST_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Hanover'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        $resultsArray = $results->all();

        /** @var Location $result */
        $result = reset($resultsArray);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(52.374478, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(9.738553, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Region Hannover', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Lower Saxony', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('DE', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = next($resultsArray);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(18.384049, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-78.131485, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Hanover', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('JM', $result->getCountry()->getName());
        $this->assertEquals('JM', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = next($resultsArray);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(43.703622, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-72.288666, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Grafton County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('NH', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('US', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = next($resultsArray);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(39.806325, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-76.984273, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('York County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('PA', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('US', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = next($resultsArray);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(37.744783, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-77.446416, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Hanover County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('VA', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('US', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    public function testGeocodeWithSpecificCity(): void
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPQUEST_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuest($this->getHttpClient($_SERVER['MAPQUEST_API_KEY']), $_SERVER['MAPQUEST_API_KEY']);

        $addressBuilder = new AddressBuilder('tests');
        $addressBuilder->setLocality('Hanover');
        $address = $addressBuilder->build();

        $query = GeocodeQuery::create('foobar');
        $query = $query->withData(MapQuest::DATA_KEY_ADDRESS, $address);
        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        $resultsArray = $results->all();

        /** @var Location $result */
        $result = reset($resultsArray);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(52.374478, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(9.738553, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Region Hannover', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Lower Saxony', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('DE', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = next($resultsArray);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(43.703622, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-72.288666, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Grafton County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('NH', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('US', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = next($resultsArray);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(39.806325, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-76.984273, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('York County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('PA', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('US', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = next($resultsArray);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(40.661764, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-75.412404, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Northampton County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('PA', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('US', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = next($resultsArray);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(40.651401, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-75.440663, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Lehigh County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('PA', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('US', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    public function testGeocodeWithSpecificCityAndBounds(): void
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPQUEST_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuest($this->getHttpClient($_SERVER['MAPQUEST_API_KEY']), $_SERVER['MAPQUEST_API_KEY']);

        $addressBuilder = new AddressBuilder('tests');
        $addressBuilder->setLocality('Hanover');
        $address = $addressBuilder->build();

        $query = GeocodeQuery::create('foobar');
        $query = $query->withData(MapQuest::DATA_KEY_ADDRESS, $address);
        $query = $query->withBounds(new Bounds(39, -77, 41, -75));
        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        $resultsArray = $results->all();

        /** @var Location $result */
        $result = reset($resultsArray);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('17331', $result->getPostalCode());
        $this->assertEqualsWithDelta(39.806325, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-76.984273, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('York County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('PA', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('US', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = next($resultsArray);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(40.661764, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-75.412404, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Northampton County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('PA', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('US', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = next($resultsArray);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(40.651401, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-75.440663, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Lehigh County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('PA', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('US', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = next($resultsArray);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('20794:21076', $result->getPostalCode());
        $this->assertEqualsWithDelta(39.192885, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-76.724137, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Howard County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('MD', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('US', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = next($resultsArray);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(52.374478, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(9.738553, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Region Hannover', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Lower Saxony', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('DE', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());
    }

    public function testGeocodeWithCityDistrict(): void
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPQUEST_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuest($this->getHttpClient($_SERVER['MAPQUEST_API_KEY']), $_SERVER['MAPQUEST_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(50.189062, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(8.636567, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('Kalbacher Hauptstraße 10', $result->getStreetName());
        $this->assertEquals(60437, $result->getPostalCode());
        $this->assertEquals('Frankfurt', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Frankfurt', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Hesse', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('DE', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());

        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The MapQuest provider does not support IP addresses, only street addresses.');

        $provider = new MapQuest($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The MapQuest provider does not support IP addresses, only street addresses.');

        $provider = new MapQuest($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIPv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The MapQuest provider does not support IP addresses, only street addresses.');

        $provider = new MapQuest($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithRealIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The MapQuest provider does not support IP addresses, only street addresses.');

        $provider = new MapQuest($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));
    }
}
