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
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\MapQuest\MapQuest;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class MapQuestTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName()
    {
        $provider = new MapQuest($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('map_quest', $provider->getName());
    }

    public function testGeocode()
    {
        $provider = new MapQuest($this->getMockedHttpClient('{}'), 'api_key');
        $result = $provider->geocodeQuery(GeocodeQuery::create('foobar'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGetNotRelevantData()
    {
        $json = '{"results":[{"locations":[{"street":"","postalCode":"","adminArea5":"","adminArea4":"","adminArea3":"","adminArea1":""}]}]}';

        $provider = new MapQuest($this->getMockedHttpClient($json), 'api_key');
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(11, 12));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGeocodeWithRealAddress()
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPQUEST_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuest($this->getHttpClient($_SERVER['MAPQUEST_API_KEY']), $_SERVER['MAPQUEST_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.866205, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.389089, $result->getCoordinates()->getLongitude(), '', 0.01);
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
    }

    public function testReverseWithRealCoordinates()
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
        $this->assertEquals(54.0484068, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-2.7990345, $result->getCoordinates()->getLongitude(), '', 0.001);
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

    public function testGeocodeWithCity()
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPQUEST_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuest($this->getHttpClient($_SERVER['MAPQUEST_API_KEY']), $_SERVER['MAPQUEST_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Hanover'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(52.374478, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(9.738553, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Region Hannover', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Lower Saxony', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('DE', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(52.374478000000003, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(9.7385529999999996, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Region Hannover', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Lower Saxony', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('DE', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(52.374478000000003, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(9.7385529999999996, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Region Hannover', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Lower Saxony', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('DE', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(52.374478000000003, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(9.7385529999999996, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Region Hannover', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Lower Saxony', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('DE', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());
    }

    public function testGeocodeWithCityDistrict()
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
        $this->assertEquals(50.189062, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(8.636567, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals('Kalbacher Hauptstraße 10', $result->getStreetName());
        $this->assertEquals(60437, $result->getPostalCode());
        $this->assertEquals('Frankfurt', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Hesse', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('DE', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());

        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MapQuest provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new MapQuest($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MapQuest provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new MapQuest($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MapQuest provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv4()
    {
        $provider = new MapQuest($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MapQuest provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = new MapQuest($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));
    }
}
