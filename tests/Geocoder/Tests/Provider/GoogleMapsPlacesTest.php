<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GoogleMapsPlaces;

class GoogleMapsPlacesTest extends TestCase
{
    /**
     * @var string
     */
    private $testAPIKey = 'fake_key';

    public function testGetName()
    {
        $provider = new GoogleMapsPlaces($this->getMockAdapter($this->never()), $this->testAPIKey);
        $this->assertEquals('google_maps_places', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://maps.googleapis.com/maps/api/place/textsearch/json?query=foobar&key=fake_key".
     */
    public function testGeocode()
    {
        $provider = new GoogleMapsPlaces($this->getMockAdapter(), $this->testAPIKey);
        $provider->geocode('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://maps.googleapis.com/maps/api/place/textsearch/json?query=&key=fake_key".
     */
    public function testGeocodeWithNull()
    {
        $provider = new GoogleMapsPlaces($this->getMockAdapter(), $this->testAPIKey);
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://maps.googleapis.com/maps/api/place/textsearch/json?query=&key=fake_key".
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new GoogleMapsPlaces($this->getMockAdapter(), $this->testAPIKey);
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMapsPlaces provider does not support IP addresses, only text searches for places.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new GoogleMapsPlaces($this->getMockAdapter($this->never()), $this->testAPIKey);
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMapsPlaces provider does not support IP addresses, only text searches for places.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new GoogleMapsPlaces($this->getMockAdapter($this->never()), $this->testAPIKey);
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMapsPlaces provider does not support IP addresses, only text searches for places.
     */
    public function testGeocodeWithRealIp()
    {
        $provider = new GoogleMapsPlaces($this->getAdapter(), $this->testAPIKey);
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://maps.googleapis.com/maps/api/place/textsearch/json?query=Columbia%20University%2C%20New%20York%2C%20United%20States&key=fake_key".
     */
    public function testGeocodeWithAddressGetsNullContent()
    {
        $provider = new GoogleMapsPlaces($this->getMockAdapterReturns(null), $this->testAPIKey);
        //TODO: change this to a text place search
        $provider->geocode('Columbia University, New York, United States');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://maps.googleapis.com/maps/api/place/textsearch/json?query=Columbia%20University%2C%20New%20York%2C%20United%20States&key=fake_key".
     */
    public function testGeocodeWithAddressGetsEmptyContent()
    {
        $provider = new GoogleMapsPlaces($this->getMockAdapterReturns('{"status":"OK"}'), $this->testAPIKey);
        $provider->geocode('Columbia University, New York, United States');
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceeded
     * @expectedExceptionMessage Daily quota exceeded https://maps.googleapis.com/maps/api/place/textsearch/json?query=Columbia%20University%2C%20New%20York%2C%20United%20States&key=fake_key
     */
    public function testGeocodeWithQuotaExceeded()
    {
        $provider = new GoogleMapsPlaces($this->getMockAdapterReturns('{"status":"OVER_QUERY_LIMIT"}'), $this->testAPIKey);
        $provider->geocode('Columbia University, New York, United States');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API key is invalid https://maps.googleapis.com/maps/api/place/textsearch/json?query=Columbia%20University%2C%20New%20York%2C%20United%20States&key=fake_key
     */
    public function testGeocodeWithInvalidApiKey()
    {
        $provider = new GoogleMapsPlaces($this->getMockAdapterReturns('{"error_message":"The provided API key is invalid.", "status":"REQUEST_DENIED"}'), $this->testAPIKey);
        $provider->geocode('Columbia University, New York, United States');
    }

    public function testGeocodeWithRealValidApiKey()
    {
        if (!isset($_SERVER['GOOGLE_PLACES_KEY'])) {
            $this->markTestSkipped('You need to configure the GOOGLE_PLACES_KEY value in phpunit.xml');
        }

        $provider = new GoogleMapsPlaces($this->getAdapter(), $_SERVER['GOOGLE_PLACES_KEY']);
        $results = $provider->geocode('Columbia University');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNotNull($result->getLatitude());
        $this->assertNotNull($result->getLongitude());
    }
}
