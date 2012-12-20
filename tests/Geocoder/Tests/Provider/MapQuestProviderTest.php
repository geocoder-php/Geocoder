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
        $provider = new MapQuestProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('map_quest', $provider->getName());
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not find results for given query: http://open.mapquestapi.com/geocoding/v1/address?location=foobar&outFormat=json&maxResults=1&thumbMaps=fals
     */
    public function testGetGeocodedData()
    {
        $provider = new MapQuestProvider($this->getMockAdapter());
        $provider->getGeocodedData('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://open.mapquestapi.com/geocoding/v1/address?location=10+avenue+Gambetta%2C+Paris%2C+France&outFormat=json&maxResults=1&thumbMaps=false
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new MapQuestProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $provider = new MapQuestProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertEquals(48.866205, $result['latitude'], '', 0.01);
        $this->assertEquals(2.389089, $result['longitude'], '', 0.01);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('10 Avenue Gambetta', $result['streetName']);
        $this->assertEquals(75011, $result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('Île-de-France', $result['region']);
        $this->assertEquals('France', $result['country']);

        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not find results for given query: http://open.mapquestapi.com/geocoding/v1/reverse?lat=1.000000&lng=2.000000
     */
    public function testGetReversedData()
    {
        $provider = new MapQuestProvider($this->getMockAdapter());
        $result   = $provider->getReversedData(array(1, 2));

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['county']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        $provider = new MapQuestProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getReversedData(array(54.0484068, -2.7990345));

        $this->assertEquals(54.0484068, $result['latitude'], '', 0.001);
        $this->assertEquals(-2.7990345, $result['longitude'], '', 0.001);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Mary Street', $result['streetName']);
        $this->assertEquals('LA1 1LZ', $result['zipcode']);
        $this->assertEquals('Lancaster', $result['city']);
        $this->assertEquals('Lancashire', $result['county']);
        $this->assertEquals('England', $result['region']);
        $this->assertEquals('United Kingdom', $result['country']);

        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithCity()
    {
        $provider = new MapQuestProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('Hannover');

        $this->assertNull($result['zipcode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not find results for given query: http://open.mapquestapi.com/geocoding/v1/address?location=Kalbacher+Hauptstra%C3%9Fe+10%2C+60437+Frankfurt%2C+Germany&outFormat=json&maxResults=1&thumbMaps=false
     */
    public function testGetGeocodedDataWithCityDistrict()
    {
        $provider = new MapQuestProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $provider->getGeocodedData('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MapQuestProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new MapQuestProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MapQuestProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new MapQuestProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MapQuestProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new MapQuestProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MapQuestProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new MapQuestProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }
}
