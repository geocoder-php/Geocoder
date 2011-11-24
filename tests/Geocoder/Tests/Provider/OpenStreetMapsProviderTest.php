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
        $this->assertEquals('Turku', $result['city']);
        $this->assertEquals(null, $result['region']);
        $this->assertEquals('Suomi', $result['country']);
    }

    public function testGetReversedDataWithRealCoordinates()
    {

        $this->provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter());
        $result = $this->provider->getReversedData(array('60.4539471728726', '22.2567841926781'));

        $this->assertEquals(60.4539471728726, $result['latitude']);
        $this->assertEquals(22.2567841926781, $result['longitude']);
        $this->assertEquals(20100, $result['zipcode']);
        $this->assertEquals('Turku', $result['city']);
        $this->assertEquals(null, $result['region']);
        $this->assertEquals('Suomi', $result['country']);
    }
}
