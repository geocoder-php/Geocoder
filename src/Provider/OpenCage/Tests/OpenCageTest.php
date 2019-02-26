<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\OpenCage\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\Bounds;
use Geocoder\Provider\OpenCage\Model\OpenCageAddress;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\OpenCage\OpenCage;

/**
 * @author mtm <mtm@opencagedata.com>
 */
class OpenCageTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName()
    {
        $provider = new OpenCage($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('opencage', $provider->getName());
    }

    public function testSslSchema()
    {
        $provider = new OpenCage($this->getMockedHttpClient('{}'), 'api_key');
        $result = $provider->geocodeQuery(GeocodeQuery::create('foobar'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGeocodeWithRealAddress()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getHttpClient($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(2, $results);

        /** @var OpenCageAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(OpenCageAddress::class, $result);
        $this->assertEquals(48.866205, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.389089, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.863142699999997, $result->getBounds()->getSouth());
        $this->assertEquals(2.3890394000000001, $result->getBounds()->getWest());
        $this->assertEquals(48.863242700000001, $result->getBounds()->getNorth());
        $this->assertEquals(2.3891393999999999, $result->getBounds()->getEast());
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Ile-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
        $this->assertEquals('Europe/Paris', $result->getTimezone());
        $this->assertEquals('31UDQ5519412427', $result->getMGRS());
        $this->assertEquals('JN18eu67qd', $result->getMaidenhead());
        $this->assertEquals('u09tyr78tz64jdcgfnhe', $result->getGeohash());
        $this->assertEquals('listed.emphasis.greeting', $result->getWhat3words());
        $this->assertEquals('10 Avenue Gambetta, 75020 Paris, France', $result->getFormattedAddress());
    }

    public function testReverseWithRealCoordinates()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getHttpClient($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(54.0484068, -2.7990345));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var OpenCageAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(OpenCageAddress::class, $result);
        $this->assertEquals(54.0484068, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-2.7990345, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(54.0484068, $result->getBounds()->getSouth(), '', 0.001);
        $this->assertEquals(-2.7998815, $result->getBounds()->getWest(), '', 0.001);
        $this->assertEquals(54.049472, $result->getBounds()->getNorth(), '', 0.001);
        $this->assertEquals(-2.7980925, $result->getBounds()->getEast(), '', 0.001);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Lancaster Gate', $result->getStreetName());
        $this->assertEquals('LA1 1LZ', $result->getPostalCode());
        $this->assertEquals('Lancaster', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Lancashire', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('England', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountry()->getCode());
        $this->assertEquals('Europe/London', $result->getTimezone());
        $this->assertEquals('30UWE1316588979', $result->getMGRS());
        $this->assertEquals('IO84ob41dr', $result->getMaidenhead());
        $this->assertEquals('gcw52r3csd02c23bwucn', $result->getGeohash());
        $this->assertEquals('heave.dock.wage', $result->getWhat3words());
        $this->assertEquals('Saint Nicholas Arcades, Lancaster Gate, Lancaster LA1 1LZ, United Kingdom', $result->getFormattedAddress());
    }

    public function testReverseWithVillage()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getHttpClient($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(49.1390924, 1.6572462));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var OpenCageAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(OpenCageAddress::class, $result);
        $this->assertEquals('Bray-et-Lû', $result->getLocality());
    }

    public function testGeocodeWithCity()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getHttpClient($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Hanover'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var OpenCageAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(OpenCageAddress::class, $result);
        $this->assertEquals(52.374478, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(9.738553, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Region Hannover', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Lower Saxony', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Germany', $result->getCountry()->getName());

        /** @var OpenCageAddress $result */
        $result = $results->get(1);
        $this->assertInstanceOf(OpenCageAddress::class, $result);
        $this->assertEquals(18.3840489, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-78.131485, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNull($result->getLocality());
        $this->assertTrue($result->getAdminLevels()->has(2));
        $this->assertEquals('Hanover', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Jamaica', $result->getCountry()->getName());

        /** @var OpenCageAddress $result */
        $result = $results->get(2);
        $this->assertInstanceOf(OpenCageAddress::class, $result);
        $this->assertEquals(43.7033073, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-72.2885663, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Grafton County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('New Hampshire', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States of America', $result->getCountry()->getName());
    }

    public function testGeocodeWithCityDistrict()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getHttpClient($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(2, $results);

        /** @var OpenCageAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(OpenCageAddress::class, $result);
        $this->assertEquals(50.189062, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(8.636567, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('Kalbacher Hauptstraße', $result->getStreetName());
        $this->assertEquals(60437, $result->getPostalCode());
        $this->assertEquals('Frankfurt', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Hesse', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Germany', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());
        $this->assertEquals('Europe/Berlin', $result->getTimezone());
    }

    public function testGeocodeWithLocale()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getHttpClient($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('London')->withLocale('es'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var OpenCageAddress $result */
        $result = $results->first();
        $this->assertInstanceOf(OpenCageAddress::class, $result);
        $this->assertEquals('Londres', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Londres', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Inglaterra', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Reino Unido', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountry()->getCode());
    }

    public function testAmbiguousResultCountryCode()
    {
        $provider = new OpenCage($this->getHttpClient($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Gera-Ost Gera  07546 DE'));

        $this->assertCount(2, $results);
        /** @var OpenCageAddress $result */
        $result = $results->first();
        $this->assertEquals('ID', $result->getCountry()->getCode());

        $results = $provider->geocodeQuery(GeocodeQuery::create('Gera-Ost Gera  07546 DE')->withData('countrycode', 'DE'));

        $this->assertCount(1, $results);
        /** @var OpenCageAddress $result */
        $result = $results->first();
        $this->assertEquals('DE', $result->getCountry()->getCode());
    }

    public function testAmbiguousResultBounds()
    {
        $provider = new OpenCage($this->getHttpClient($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Gera-Ost Gera  07546 DE'));

        $this->assertCount(2, $results);
        /** @var OpenCageAddress $result */
        $result = $results->first();
        $this->assertEquals('ID', $result->getCountry()->getCode());

        $bounds = new Bounds(50.8613807, 11.7525627, 50.8850706, 12.511183);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Gera-Ost Gera  07546 DE')->withBounds($bounds));

        $this->assertCount(1, $results);
        /** @var OpenCageAddress $result */
        $result = $results->first();
        $this->assertEquals('DE', $result->getCountry()->getCode());
    }

    public function testAmbiguousResultProximity()
    {
        $provider = new OpenCage($this->getHttpClient($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('odessa'));

        /** @var OpenCageAddress $result */
        $result = $results->first();
        $this->assertEquals('UA', $result->getCountry()->getCode());

        $results = $provider->geocodeQuery(GeocodeQuery::create('odessa')->withData('proximity', '31.918807,-102.474021'));

        /** @var OpenCageAddress $result */
        $result = $results->first();
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceeded
     * @expectedExceptionMessage Valid request but quota exceeded.
     */
    public function testGeocodeQuotaExceeded()
    {
        $provider = new OpenCage(
            $this->getMockedHttpClient(
                '{
                    "status": {
                        "code": 402,
                        "message": "quota exceeded"
                    }
                }'
            ),
            'api_key'
        );
        $provider->geocodeQuery(GeocodeQuery::create('New York'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage Invalid or missing api key.
     */
    public function testGeocodeInvalidApiKey()
    {
        $provider = new OpenCage(
            $this->getMockedHttpClient(
                '{
                    "status": {
                        "code": 403,
                        "message": "invalid API key"
                    }
                }'
            ),
            'api_key'
        );
        $provider->geocodeQuery(GeocodeQuery::create('New York'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OpenCage provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new OpenCage($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OpenCage provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new OpenCage($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OpenCage provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv4()
    {
        $provider = new OpenCage($this->getHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OpenCage provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = new OpenCage($this->getHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));
    }
}
