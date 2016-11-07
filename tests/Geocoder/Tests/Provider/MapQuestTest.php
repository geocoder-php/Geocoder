<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Location;
use Geocoder\Tests\TestCase;
use Geocoder\Provider\MapQuest;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class MapQuestTest extends TestCase
{
    public function testGetName()
    {
        $provider = new MapQuest($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('map_quest', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not find results for query "http://open.mapquestapi.com/geocoding/v1/address?location=foobar&outFormat=json&maxResults=5&key=api_key&thumbMaps=false".
     */
    public function testGeocode()
    {
        $provider = new MapQuest($this->getMockAdapterReturns('{}'), 'api_key');
        $provider->geocode('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://open.mapquestapi.com/geocoding/v1/address?location=10+avenue+Gambetta%2C+Paris%2C+France&outFormat=json&maxResults=5&key=api_key&thumbMaps=false".
     */
    public function testGeocodeWithAddressGetsNullContent()
    {
        $provider = new MapQuest($this->getMockAdapterReturns(null), 'api_key');
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not find results for query "http://open.mapquestapi.com/geocoding/v1/reverse?key=api_key&lat=123.000000&lng=456.000000".
     */
    public function testGetNotRelevantData()
    {
        $json = '{"results":[{"locations":[{"street":"","postalCode":"","adminArea5":"","adminArea4":"","adminArea3":"","adminArea1":""}]}]}';

        $provider = new MapQuest($this->getMockAdapterReturns($json), 'api_key');
        $provider->reverse(123, 456);
    }

    public function testGeocodeWithRealAddress()
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPQUEST_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuest($this->getAdapter($_SERVER['MAPQUEST_API_KEY']), $_SERVER['MAPQUEST_API_KEY']);
        $results  = $provider->geocode('10 avenue Gambetta, Paris, France');

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

    /**
     * @expectedException \Geocoder\Exception\NoResult
     */
    public function testReverse()
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPQUEST_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuest($this->getMockAdapter(), $_SERVER['MAPQUEST_API_KEY']);
        $provider->reverse(1, 2);
    }

    public function testReverseWithRealCoordinates()
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPQUEST_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuest($this->getAdapter($_SERVER['MAPQUEST_API_KEY']), $_SERVER['MAPQUEST_API_KEY']);
        $results  = $provider->reverse(54.0484068, -2.7990345);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(54.0484068, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-2.7990345, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals('Lancaster Gate', $result->getStreetName());
        $this->assertEquals('LA1 1LZ', $result->getPostalCode());
        $this->assertEquals('Lancaster', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Lancashire', $result->getAdminLevels()->get(2)->getName());
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

        $provider = new MapQuest($this->getAdapter($_SERVER['MAPQUEST_API_KEY']), $_SERVER['MAPQUEST_API_KEY']);
        $results  = $provider->geocode('Hanover');

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

        $provider = new MapQuest($this->getAdapter($_SERVER['MAPQUEST_API_KEY']), $_SERVER['MAPQUEST_API_KEY']);
        $results  = $provider->geocode('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');

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
        $provider = new MapQuest($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MapQuest provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new MapQuest($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MapQuest provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv4()
    {
        $provider = new MapQuest($this->getAdapter(), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MapQuest provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = new MapQuest($this->getAdapter(), 'api_key');
        $provider->geocode('::ffff:74.200.247.59');
    }
}
