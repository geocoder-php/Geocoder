<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;

use Geocoder\Provider\OpenStreetMapsProvider;

class OpenStreetMapsProviderTest extends TestCase
{
    public function testGetGeocodedDataWithRealAddress()
    {
        $this->provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter());
        $result = $this->provider->getGeocodedData('Läntinen Pitkäkatu 35, Turku');

        $this->assertEquals(60.4539471768582, $result['latitude'], '', 0.0001);
        $this->assertEquals(22.2567842183875, $result['longitude'], '', 0.0001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(60.4537582397461, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(22.2563400268555, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(60.4541320800781, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(22.2572231292725, $result['bounds']['east'], '', 0.0001);
        $this->assertEquals('20100', $result['zipcode']);
        $this->assertEquals(35, $result['streetNumber']);
        $this->assertEquals('Läntinen Pitkäkatu', $result['streetName']);
        $this->assertEquals('Turku', $result['city']);
        $this->assertEquals('Varsinais-Suomi', $result['county']);
        $this->assertEquals(null, $result['region']);
        $this->assertEquals('Finland', $result['country']);
        $this->assertEquals('FI', $result['countryCode']);

        $result = $this->provider->getGeocodedData('10 allée Evariste Galois, Clermont ferrand');

        $this->assertEquals(45.7587754693841, $result['latitude'], '', 0.01);
        $this->assertEquals(3.13073228527092, $result['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(45.7595672607422, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(3.12900018692017, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(45.7605743408203, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(3.1324610710144, $result['bounds']['east'], '', 0.01);
        $this->assertEquals(null, $result['streetNumber']);
        $this->assertEquals('Allée Évariste Galois', $result['streetName']);
        $this->assertEquals('63170', $result['zipcode']);
        //$this->assertEquals('Clermont-Ferrand', $result['city']);
        $this->assertEquals('Puy-de-Dôme', $result['county']);
        $this->assertEquals('Auvergne', $result['region']);
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        $this->provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter());
        $result = $this->provider->getReversedData(array('60.4539471728726', '22.2567841926781'));

        $this->assertEquals(60.4539471768582, $result['latitude'], '', 0.0001);
        $this->assertEquals(22.2567842183875, $result['longitude'], '', 0.0001);
        $this->assertEquals(35, $result['streetNumber']);
        $this->assertEquals('Läntinen Pitkäkatu', $result['streetName']);
        $this->assertEquals(20100, $result['zipcode']);
        $this->assertEquals('Turku', $result['city']);
        $this->assertEquals('Varsinais-Suomi', $result['county']);
        $this->assertEquals(null, $result['region']);
        $this->assertEquals('Finland', $result['country']);
        $this->assertEquals('FI', $result['countryCode']);
    }

    public function testGetGeocodedDataWithUnknownCity()
    {
        $this->provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter());
        $result = $this->provider->getGeocodedData('Hammm');

        $this->assertNull($result['city']);
    }

    public function testGetGeocodedDataWithCityDistrict()
    {
        $this->provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter());
        $result = $this->provider->getGeocodedData('Kalbacher Hauptstraße, 60437 Frankfurt, Germany');

        $this->assertNotNull('Kalbach', $result['cityDistrict']);
    }
}
