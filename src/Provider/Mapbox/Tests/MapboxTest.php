<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Mapbox\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\Bounds;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\Mapbox\Mapbox;

class MapboxTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        if (isset($_SERVER['USE_CACHED_RESPONSES']) && true === $_SERVER['USE_CACHED_RESPONSES']) {
            return __DIR__.'/.cached_responses';
        }

        return null;
    }

    public function testGetName()
    {
        $provider = new Mapbox($this->getMockedHttpClient(), 'access_token');
        $this->assertEquals('mapbox', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new Mapbox($this->getMockedHttpClient(), 'access_token');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Mapbox provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new Mapbox($this->getMockedHttpClient(), 'access_token');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Mapbox provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIp()
    {
        $provider = new Mapbox($this->getHttpClient(), 'access_token');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceeded
     */
    public function testGeocodeWithQuotaExceeded()
    {
        $provider = new Mapbox($this->getMockedHttpClient('', 429), 'access_token');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodePlaceWithNoCountryShortCode()
    {
        $provider = new Mapbox($this->getHttpClient($_SERVER['MAPBOX_GEOCODING_KEY']), $_SERVER['MAPBOX_GEOCODING_KEY']);

        $query = GeocodeQuery::create('princ'); // Principato di Monaco
        $query = $query->withLocale('it');
        $query = $query->withBounds(new Bounds(
            35.82809688193029,
            -11.36323261153737,
            59.05992036364424,
            34.33947713277206
        ));
        $query = $query->withLimit(1);
        $query = $query->withData('location_type', [
            Mapbox::TYPE_PLACE,
            Mapbox::TYPE_LOCALITY,
            Mapbox::TYPE_NEIGHBORHOOD,
            Mapbox::TYPE_POI,
            Mapbox::TYPE_POI_LANDMARK,
        ]);

        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals(43.73125, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(7.41974, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals('Principato di Monaco', $result->getStreetName());
        $this->assertEquals('Principato di Monaco', $result->getCountry()->getName());
        $this->assertEquals('place.4899176537126140', $result->getId());

        // not provided
        $this->assertNull($result->getCountry()->getCode());
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getLocality());
        $this->assertNull($result->getTimezone());
        $this->assertNull($result->getStreetNumber());
    }

    public function testGeocodeWithRealAddress()
    {
        if (!isset($_SERVER['MAPBOX_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPBOX_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new Mapbox($this->getHttpClient($_SERVER['MAPBOX_GEOCODING_KEY']), $_SERVER['MAPBOX_GEOCODING_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('149 9th St, San Francisco, CA 94103'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals(37.77572, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-122.41362, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNull($result->getBounds());
        $this->assertEquals(149, $result->getStreetNumber());
        $this->assertEquals('9th Street', $result->getStreetName());
        $this->assertEquals(94103, $result->getPostalCode());
        $this->assertEquals('San Francisco', $result->getLocality());
        $this->assertEquals('California', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('CA', $result->getAdminLevels()->get(2)->getCode());
        $this->assertEquals('San Francisco', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('address.3071152063251042', $result->getId());

        // not provided
        $this->assertNull($result->getTimezone());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testReverse()
    {
        $provider = new Mapbox($this->getMockedHttpClient(), 'access_token');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    public function testReverseWithRealCoordinates()
    {
        if (!isset($_SERVER['MAPBOX_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPBOX_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new Mapbox($this->getHttpClient($_SERVER['MAPBOX_GEOCODING_KEY']), $_SERVER['MAPBOX_GEOCODING_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.8631507, 2.388911));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(4, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals(8, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
        $this->assertEquals('address.1085979616', $result->getId());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     */
    public function testGeocodeWithInvalidApiKey()
    {
        $provider = new Mapbox($this->getMockedHttpClient('', 403), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithRealValidApiKey()
    {
        if (!isset($_SERVER['MAPBOX_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPBOX_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new Mapbox($this->getHttpClient($_SERVER['MAPBOX_GEOCODING_KEY']), $_SERVER['MAPBOX_GEOCODING_KEY']);

        $results = $provider->geocodeQuery(GeocodeQuery::create('116th St & Broadway, New York, NY 10027, United States'));

        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals('116th Street', $result->getStreetName());
        $this->assertEquals(11356, $result->getPostalCode());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('address.2431617896783536', $result->getId());
        $this->assertNotNull($result->getCoordinates()->getLatitude());
        $this->assertNotNull($result->getCoordinates()->getLongitude());
        $this->assertEquals(40.786596, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-73.851157, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals('New York', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('New York', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('NY', $result->getAdminLevels()->get(2)->getCode());
    }

    public function testGeocodeWithFuzzyMatch()
    {
        if (!isset($_SERVER['MAPBOX_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPBOX_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new Mapbox($this->getHttpClient($_SERVER['MAPBOX_GEOCODING_KEY']), $_SERVER['MAPBOX_GEOCODING_KEY']);

        $query = GeocodeQuery::create('wahsington'); // Washington
        $query = $query->withData('fuzzy_match', true);
        $query = $query->withBounds(new Bounds(
            45.54372254,
            -124.83609163,
            49.00243912,
            -116.91742984
        ));

        $results = $provider->geocodeQuery($query);
        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertCount(5, $results);
    }

    public function testGeocodeWithoutFuzzyMatch()
    {
        if (!isset($_SERVER['MAPBOX_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPBOX_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new Mapbox($this->getHttpClient($_SERVER['MAPBOX_GEOCODING_KEY']), $_SERVER['MAPBOX_GEOCODING_KEY']);

        $query = GeocodeQuery::create('wahsington'); // Washington
        $query = $query->withData('fuzzy_match', false);
        $query = $query->withBounds(new Bounds(
            45.54372254,
            -124.83609163,
            49.00243912,
            -116.91742984
        ));

        $results = $provider->geocodeQuery($query);
        $this->assertInstanceOf(AddressCollection::class, $results);
        $this->assertEmpty($results);
    }
}
