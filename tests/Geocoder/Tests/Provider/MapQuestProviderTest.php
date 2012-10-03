<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Provider\MapQuestProvider;
use Geocoder\Tests\TestCase;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class MapQuestProviderTest extends TestCase
{
    public function testGetGeocodedData()
    {
        $provider = new MapQuestProvider($this->getMockAdapter());
        $result   = $provider->getGeocodedData('foobar');

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

    public function testGetGeocodedDataWithRealAddress()
    {
        $provider = new MapQuestProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertEquals(48.866205, $result['latitude'], '', 0.01);
        $this->assertEquals(2.399611, $result['longitude'], '', 0.01);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Avenue Gambetta', $result['streetName']);
        $this->assertEmpty($result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEmpty($result['county']);
        $this->assertEquals('Île-de-France', $result['region']);
        $this->assertEquals('France', $result['country']);

        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

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
        $this->assertEmpty($result['streetName']);
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

    public function testGetGeocodedDataWithCityDistrict()
    {
        $provider = new MapQuestProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');

        $this->assertNull($result['cityDistrict']);
    }
}
