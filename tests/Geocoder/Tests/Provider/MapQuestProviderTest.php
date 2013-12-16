<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\MapQuestProvider;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class MapQuestProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new MapQuestProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('map_quest', $provider->getName());
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not find results for given query: http://open.mapquestapi.com/geocoding/v1/address?location=foobar&outFormat=json&maxResults=5&key=api_key&thumbMaps=false
     */
    public function testGetGeocodedData()
    {
        $provider = new MapQuestProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query: http://open.mapquestapi.com/geocoding/v1/address?location=10+avenue+Gambetta%2C+Paris%2C+France&outFormat=json&maxResults=5&key=api_key&thumbMaps=false
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new MapQuestProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuestProvider($this->getAdapter(), $_SERVER['MAPQUEST_API_KEY']);
        $results  = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(48.866205, $result['latitude'], '', 0.01);
        $this->assertEquals(2.389089, $result['longitude'], '', 0.01);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('10 Avenue Gambetta', $result['streetName']);
        $this->assertEquals(75011, $result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('Ile-de-France', $result['region']);
        $this->assertEquals('FR', $result['country']);

        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     */
    public function testGetReversedData()
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuestProvider($this->getMockAdapter(), $_SERVER['MAPQUEST_API_KEY']);
        $provider->getReversedData(array(1, 2));
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuestProvider($this->getAdapter(), $_SERVER['MAPQUEST_API_KEY']);
        $result   = $provider->getReversedData(array(54.0484068, -2.7990345));

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(54.0484068, $result['latitude'], '', 0.001);
        $this->assertEquals(-2.7990345, $result['longitude'], '', 0.001);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Mary Street', $result['streetName']);
        $this->assertEquals('LA1 1LZ', $result['zipcode']);
        $this->assertEquals('Lancaster', $result['city']);
        $this->assertEquals('Lancashire', $result['county']);
        $this->assertEquals('England', $result['region']);
        $this->assertEquals('GB', $result['country']);

        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithCity()
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuestProvider($this->getAdapter(), $_SERVER['MAPQUEST_API_KEY']);
        $results  = $provider->getGeocodedData('Hanover');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(52.374478, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(9.738553, $results[0]['longitude'], '', 0.01);
        $this->assertEquals('Hanover', $results[0]['city']);
        $this->assertEquals('Region Hannover', $results[0]['county']);
        $this->assertEquals('Niedersachsen (Landmasse)', $results[0]['region']);
        $this->assertEquals('DE', $results[0]['country']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(18.383715, $results[1]['latitude'], '', 0.01);
        $this->assertEquals(-78.131484, $results[1]['longitude'], '', 0.01);
        $this->assertNull($results[1]['city']);
        $this->assertEquals('Hanover', $results[1]['county']);
        $this->assertEquals('JM', $results[1]['country']);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(43.703307, $results[2]['latitude'], '', 0.01);
        $this->assertEquals(-72.288566, $results[2]['longitude'], '', 0.01);
        $this->assertEquals('Hanover', $results[2]['city']);
        $this->assertEquals('Grafton County', $results[2]['county']);
        $this->assertEquals('NH', $results[2]['region']);
        $this->assertEquals('US', $results[2]['country']);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(39.806325, $results[3]['latitude'], '', 0.01);
        $this->assertEquals(-76.984274, $results[3]['longitude'], '', 0.01);
        $this->assertEquals('Hanover', $results[3]['city']);
        $this->assertEquals('York County', $results[3]['county']);
        $this->assertEquals('PA', $results[3]['region']);
        $this->assertEquals('US', $results[3]['country']);
    }

    public function testGetGeocodedDataWithCityDistrict()
    {
        if (!isset($_SERVER['MAPQUEST_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new MapQuestProvider($this->getAdapter(), $_SERVER['MAPQUEST_API_KEY']);
        $result   = $provider->getGeocodedData('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(50.189062, $result['latitude'], '', 0.01);
        $this->assertEquals(8.636567, $result['longitude'], '', 0.01);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Kalbacher Hauptstraße 10', $result['streetName']);
        $this->assertEquals(60437, $result['zipcode']);
        $this->assertEquals('Frankfurt', $result['city']);
        $this->assertEquals('Frankfurt', $result['county']);
        $this->assertEquals('Hesse', $result['region']);
        $this->assertEquals('DE', $result['country']);

        $this->assertNull($result['countryCode']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MapQuestProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new MapQuestProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MapQuestProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new MapQuestProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MapQuestProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new MapQuestProvider($this->getAdapter(), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MapQuestProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new MapQuestProvider($this->getAdapter(), 'api_key');
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }
}
