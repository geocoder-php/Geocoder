<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GoogleMapsProvider;

class GoogleMapsProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new GoogleMapsProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('google_maps', $provider->getName());
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://maps.googleapis.com/maps/api/geocode/json?address=foobar&sensor=false
     */
    public function testGetGeocodedData()
    {
        $provider = new GoogleMapsProvider($this->getMockAdapter());
        $provider->getGeocodedData('foobar');
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://maps.googleapis.com/maps/api/geocode/json?address=&sensor=false
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new GoogleMapsProvider($this->getMockAdapter());
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://maps.googleapis.com/maps/api/geocode/json?address=&sensor=false
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new GoogleMapsProvider($this->getMockAdapter());
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GoogleMapsProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new GoogleMapsProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GoogleMapsProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new GoogleMapsProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GoogleMapsProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIp()
    {
        $provider = new GoogleMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France&sensor=false
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new GoogleMapsProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France&sensor=false
     */
    public function testGetGeocodedDataWithAddressGetsEmptyContent()
    {
        $provider = new GoogleMapsProvider($this->getMockAdapterReturns('{"status":"OK"}'));
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $provider = new GoogleMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'fr-FR', 'Île-de-France');
        $result   = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertEquals(48.8630462, $result['latitude'], '', 0.001);
        $this->assertEquals(2.3882487, $result['longitude'], '', 0.001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(48.8630462, $result['bounds']['south'], '', 0.001);
        $this->assertEquals(2.3882487, $result['bounds']['west'], '', 0.001);
        $this->assertEquals(48.8630462, $result['bounds']['north'], '', 0.001);
        $this->assertEquals(2.3882487, $result['bounds']['east'], '', 0.001);
        $this->assertEquals(10, $result['streetNumber']);
        $this->assertEquals('Avenue Gambetta', $result['streetName']);
        $this->assertEquals(75020, $result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('Île-de-France', $result['region']);
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);

        // not provided
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataBoundsWithRealAddressForNonRooftopLocation()
    {
        $provider = new GoogleMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('Paris, France');

        $this->assertNotNull($result['bounds']);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(48.815573, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(2.224199, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(48.902145, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(2.4699209, $result['bounds']['east'], '', 0.0001);
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://maps.googleapis.com/maps/api/geocode/json?address=1.000000%2C2.000000&sensor=false
     */
    public function testGetReversedData()
    {
        $provider = new GoogleMapsProvider($this->getMockAdapter());
        $provider->getReversedData(array(1, 2));
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        $provider = new GoogleMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getReversedData(array(48.8631507, 2.388911));

        $this->assertEquals(10, $result['streetNumber']);
        $this->assertEquals('Avenue Gambetta', $result['streetName']);
        $this->assertEquals(75020, $result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('Île-de-France', $result['region']);
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://maps.googleapis.com/maps/api/geocode/json?address=48.863151%2C2.388911&sensor=false
     */
    public function testGetReversedDataWithCoordinatesGetsNullContent()
    {
        $provider = new GoogleMapsProvider($this->getMockAdapterReturns(null));
        $provider->getReversedData(array(48.8631507, 2.388911));
    }

    public function testGetGeocodedDataWithCityDistrict()
    {
        $provider = new GoogleMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');

        $this->assertEquals('Kalbach-Riedberg', $result['cityDistrict']);
    }
}
