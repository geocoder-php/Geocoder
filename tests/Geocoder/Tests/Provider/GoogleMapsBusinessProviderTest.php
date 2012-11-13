<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GoogleMapsBusinessProvider;

class GoogleMapsBusinessProviderTest extends TestCase
{
    private $testClientId = 'foo';
    private $testPrivateKey = 'bogus';

    public function testGetName()
    {
        $provider = new GoogleMapsBusinessProvider($this->getMockAdapter($this->never()), $this->testClientId);
        $this->assertEquals('google_maps_business', $provider->getName());
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://maps.googleapis.com/maps/api/geocode/json?address=foobar&sensor=false
     */
    public function testGetGeocodedData()
    {
        $provider = new GoogleMapsBusinessProvider($this->getMockAdapter(), $this->testClientId);
        $provider->getGeocodedData('foobar');
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://maps.googleapis.com/maps/api/geocode/json?address=10+avenue+Gambetta%2C+Paris%2C+France&sensor=false&client=foo&signature=z_AbLZ6L0Pr_JV3aCdtGWXI7Q8Y=
     */
    public function testGetGeocodedDataWithPrivateKey()
    {
        $provider = new GoogleMapsBusinessProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $this->testClientId, $this->testPrivateKey);
        $result   = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['GOOGLEMAPS_BUSINESS_CLIENT_ID']) || !isset($_SERVER['GOOGLEMAPS_BUSINESS_PRIVATE_KEY'])) {
            $this->markTestSkipped('You need to configure the GOOGLEMAPS_BUSINESS_CLIENT_ID and GOOGLEMAPS_BUSINESS_PRIVATE_KEY values in phpunit.xml');
        }

        $provider = new GoogleMapsBusinessProvider(
            new \Geocoder\HttpAdapter\CurlHttpAdapter(),
            $_SERVER['GOOGLEMAPS_BUSINESS_CLIENT_ID'],
            $_SERVER['GOOGLEMAPS_BUSINESS_PRIVATE_KEY'],
            'fr-FR',
            'fr'
        );
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

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['GOOGLEMAPS_BUSINESS_CLIENT_ID']) || !isset($_SERVER['GOOGLEMAPS_BUSINESS_PRIVATE_KEY'])) {
            $this->markTestSkipped('You need to configure the GOOGLEMAPS_BUSINESS_CLIENT_ID and GOOGLEMAPS_BUSINESS_PRIVATE_KEY values in phpunit.xml');
        }

        $provider = new GoogleMapsBusinessProvider(
            new \Geocoder\HttpAdapter\CurlHttpAdapter(),
            $_SERVER['GOOGLEMAPS_BUSINESS_CLIENT_ID'],
            $_SERVER['GOOGLEMAPS_BUSINESS_PRIVATE_KEY']
        );
        $result = $provider->getReversedData(array(48.8631507, 2.388911));

        $this->assertEquals(10, $result['streetNumber']);
        $this->assertEquals('Avenue Gambetta', $result['streetName']);
        $this->assertEquals(75020, $result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('Île-de-France', $result['region']);
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
    }
}
