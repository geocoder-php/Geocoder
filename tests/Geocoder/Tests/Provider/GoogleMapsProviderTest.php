<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;

use Geocoder\Provider\GoogleMapsProvider;

class GoogleMapsProviderTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new GoogleMapsProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    public function testGetGeocodedData()
    {
        $this->provider = new GoogleMapsProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('foobar');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetGeocodedDataWithNull()
    {
        $this->provider = new GoogleMapsProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData(null);

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetGeocodedDataWithEmpty()
    {
        $this->provider = new GoogleMapsProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetGeocodedDataWithLocalhost()
    {
        $this->provider = new GoogleMapsProvider($this->getMockAdapter($this->never()), 'api_key');
        $result = $this->provider->getGeocodedData('127.0.0.1');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['zipcode']);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['country']);
    }

    public function testGetGeocodedDataWithRealIp()
    {
        if (!isset($_SERVER['GOOGLEMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the GOOGLEMAPS_API_KEY value in phpunit.xml');
        }

        $this->provider = new GoogleMapsProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['GOOGLEMAPS_API_KEY']);
        $result = $this->provider->getGeocodedData('74.200.247.59');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['GOOGLEMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the GOOGLEMAPS_API_KEY value in phpunit.xml');
        }

        $this->provider = new GoogleMapsProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['GOOGLEMAPS_API_KEY']);
        $result = $this->provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertEquals(48.8631507, $result['latitude']);
        $this->assertEquals(2.3889114, $result['longitude']);
        $this->assertEquals(75020, $result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Île-de-France', $result['region']);
        $this->assertEquals('France', $result['country']);
    }

    public function testGetReversedData()
    {
        $this->provider = new GoogleMapsProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getReversedData(array(1, 2));

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['GOOGLEMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the GOOGLEMAPS_API_KEY value in phpunit.xml');
        }

        $this->provider = new GoogleMapsProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['GOOGLEMAPS_API_KEY']);
        $result = $this->provider->getReversedData(array(48.8631507, 2.388911));

        $this->assertEquals(48.8631507, $result['latitude']);
        $this->assertEquals(2.3889114, $result['longitude']);
        $this->assertEquals(75020, $result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Île-de-France', $result['region']);
        $this->assertEquals('France', $result['country']);
    }
}
