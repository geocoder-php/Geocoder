<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\Mapzen;

/**
 * @author Gary Gale <gary@vicchi.org>
 */
class MapzenTest extends TestCase
{
    public function testGetName()
    {
        $provider = new Mapzen($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('mapzen', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not find results for query "https://search.mapzen.com/v1/search?text=foobar&key=api_key&size=5".
     */
    public function testGeocode()
    {
        $provider = new Mapzen($this->getMockAdapterReturns('{}'), 'api_key');
        $provider->geocode('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not find results for query "https://search.mapzen.com/v1/search?text=foobar&key=api_key&size=5".
     */
    public function testSslSchema()
    {
        $provider = new Mapzen($this->getMockAdapterReturns('{}'), 'api_key', true);
        $provider->geocode('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://search.mapzen.com/v1/search?text=242+Acklam+Road%2C+London%2C+United+Kingdom&key=api_key&size=5".
     */
    public function testGeocodeWithAddressGetsNullContent()
    {
        $provider = new Mapzen($this->getMockAdapterReturns(null), 'api_key');
        $provider->geocode('242 Acklam Road, London, United Kingdom');
    }

    public function testGeocodeWithRealAddress()
    {
        if (!isset($_SERVER['MAPZEN_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPZEN_API_KEY value in phpunit.xml');
        }

        $provider = new Mapzen($this->getAdapter($_SERVER['MAPZEN_API_KEY']), $_SERVER['MAPZEN_API_KEY']);
        $results  = $provider->geocode('242 Acklam Road, London, United Kingdom');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(51.521124, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-0.20360200000000001, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals(240, $result->getStreetNumber());
        $this->assertEquals('Acklam Road', $result->getStreetName());
        $this->assertEquals('W10 5QT', $result->getPostalCode());
        $this->assertEquals('London', $result->getLocality());
        $this->assertCount(4, $result->getAdminLevels());
        $this->assertEquals('London', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Kensington and Chelsea', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());
        $this->assertEquals('GBR', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     */
    public function testReverse()
    {
        if (!isset($_SERVER['MAPZEN_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPZEN_API_KEY value in phpunit.xml');
        }

        $provider = new Mapzen($this->getMockAdapter(), $_SERVER['MAPZEN_API_KEY']);
        $provider->reverse(1, 2);
    }

    public function testReverseWithRealCoordinates()
    {
        if (!isset($_SERVER['MAPZEN_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPZEN_API_KEY value in phpunit.xml');
        }

        $provider = new Mapzen($this->getAdapter($_SERVER['MAPZEN_API_KEY']), $_SERVER['MAPZEN_API_KEY']);
        $results  = $provider->reverse(54.0484068, -2.7990345);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(54.048411999999999, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-2.7989549999999999, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Gage Street', $result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Lancaster', $result->getLocality());
        $this->assertCount(4, $result->getAdminLevels());
        $this->assertEquals('Lancashire', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('England', $result->getAdminLevels()->get(3)->getName());
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());
        $this->assertEquals('GBR', $result->getCountry()->getCode());
    }

    public function testReverseWithVillage()
    {
        if (!isset($_SERVER['MAPZEN_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPZEN_API_KEY value in phpunit.xml');
        }

        $provider = new Mapzen($this->getAdapter($_SERVER['MAPZEN_API_KEY']), $_SERVER['MAPZEN_API_KEY']);
        $results  = $provider->reverse(49.1390924, 1.6572462);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Bus-Saint-Rémy', $result->getLocality());
    }

    public function testGeocodeWithCity()
    {
        if (!isset($_SERVER['MAPZEN_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPZEN_API_KEY value in phpunit.xml');
        }

        $provider = new Mapzen($this->getAdapter($_SERVER['MAPZEN_API_KEY']), $_SERVER['MAPZEN_API_KEY']);
        $results  = $provider->geocode('Hanover');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(42.027323000000003, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-88.204203000000007, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNull($result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('United States', $result->getAdminLevels()->get(4)->getName());
        $this->assertEquals('Illinois', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(18.393428, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-78.122906, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNull($result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Hanover', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Jamaica', $result->getCountry()->getName());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(39.192889999999998, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-76.724140000000006, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertTrue( $result->getAdminLevels()->has(4));
        $this->assertEquals('Hanover', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
    }

    public function testGeocodeWithCityDistrict()
    {
        if (!isset($_SERVER['MAPZEN_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPZEN_API_KEY value in phpunit.xml');
        }

        $provider = new Mapzen($this->getAdapter($_SERVER['MAPZEN_API_KEY']), $_SERVER['MAPZEN_API_KEY']);
        $results  = $provider->geocode('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(50.189017, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(8.6367809999999992, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals('10a', $result->getStreetNumber());
        $this->assertEquals('Kalbacher Hauptstraße', $result->getStreetName());
        $this->assertEquals(60437, $result->getPostalCode());
        $this->assertEquals('Frankfurt am Main', $result->getLocality());
        $this->assertCount(3, $result->getAdminLevels());
        $this->assertEquals('Frankfurt am Main', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Hessen', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Germany', $result->getCountry()->getName());
        $this->assertEquals('DEU', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceeded
     * @expectedExceptionMessage Valid request but quota exceeded.
     */
    public function testGeocodeQuotaExceeded()
    {
        $provider = new Mapzen(
            $this->getMockAdapterReturns(
                '{
                    "meta": {
                        "version": 1,
                        "status_code": 429
                    },
                    "results": {
                        "error": {
                            "type": "QpsExceededError",
                            "message": "Queries per second exceeded: Queries exceeded (6 allowed)."
                        }
                    }
                }'
            ),
            'api_key'
        );
        $provider->geocode('New York');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage Invalid or missing api key.
     */
    public function testGeocodeInvalidApiKey()
    {
        $provider = new Mapzen(
            $this->getMockAdapterReturns(
                '{
                    "meta": {
                        "version": 1,
                        "status_code": 403
                    },
                    "results": {
                        "error": {
                            "type": "KeyError",
                            "message": "No api_key specified."
                        }
                    }
                }'
            ),
            'api_key'
        );
        $provider->geocode('New York');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Mapzen provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new Mapzen($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Mapzen provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new Mapzen($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Mapzen provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv4()
    {
        $provider = new Mapzen($this->getAdapter(), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Mapzen provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = new Mapzen($this->getAdapter(), 'api_key');
        $provider->geocode('::ffff:74.200.247.59');
    }
}
