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

        $this->assertEquals('60.4539471728726', $result['latitude']);
        $this->assertEquals('22.2567841926781', $result['longitude']);
        $this->assertEquals('20100', $result['zipcode']);
        $this->assertEquals(35, $result['streetNumber']);
        $this->assertEquals('Läntinen Pitkäkatu', $result['streetName']);
        $this->assertEquals('Turku', $result['city']);
        $this->assertEquals(null, $result['county']);
        $this->assertEquals(null, $result['region']);
        $this->assertEquals('Suomi', $result['country']);

        $result = $this->provider->getGeocodedData('10 allée Evariste Galois, Clermont ferrand');

        $this->assertEquals('45.7595505', $result['latitude']);
        $this->assertEquals('3.1325205', $result['longitude']);
        $this->assertEquals(null, $result['streetNumber']);
        $this->assertEquals('Allée Évariste Galois', $result['streetName']);
        $this->assertEquals('63170', $result['zipcode']);
        $this->assertEquals('Clermont-Ferrand', $result['city']);
        $this->assertEquals('Puy-de-Dôme', $result['county']);
        $this->assertEquals('Auvergne', $result['region']);
        $this->assertEquals('France', $result['country']);
    }

    public function testGetReversedDataWithRealCoordinates()
    {

        $this->provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter());
        $result = $this->provider->getReversedData(array('60.4539471728726', '22.2567841926781'));

        $this->assertEquals(60.4539471728726, $result['latitude']);
        $this->assertEquals(22.2567841926781, $result['longitude']);
        $this->assertEquals(35, $result['streetNumber']);
        $this->assertEquals('Läntinen Pitkäkatu', $result['streetName']);
        $this->assertEquals(20100, $result['zipcode']);
        $this->assertEquals('Turku', $result['city']);
        $this->assertEquals(null, $result['county']);
        $this->assertEquals(null, $result['region']);
        $this->assertEquals('Suomi', $result['country']);
    }
}
