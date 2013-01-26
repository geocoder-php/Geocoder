<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeonamesProvider;

class GeonamesProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new GeonamesProvider($this->getMockAdapter($this->never()), 'username');
        $this->assertEquals('geonames', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage No Username provided
     */
    public function testGetGeocodedDataWithNullUsername()
    {
        $provider = new GeonamesProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage No Username provided
     */
    public function testGetReversedDataWithNullUsername()
    {
        $provider = new GeonamesProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getReversedData(array(1,2));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeonamesProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new GeonamesProvider($this->getMockAdapter($this->never()), 'username');
        $provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeonamesProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new GeonamesProvider($this->getMockAdapter($this->never()), 'username');
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://api.geonames.org/searchJSON?q=&maxRows=1&style=full&username=username
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new GeonamesProvider($this->getMockAdapter(), 'username');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage No places found for query http://api.geonames.org/searchJSON?q=BlaBlaBla&maxRows=1&style=full&username=username
     * @
     */
    public function testGetGeocodedDataWithUnknownCity()
    {
        $noPlacesFoundResponse = <<<JSON
{
    "totalResultsCount": 0,
    "geonames": [ ]
}
JSON;
        $provider = new GeonamesProvider($this->getMockAdapterReturns($noPlacesFoundResponse), 'username');
        $provider->getGeocodedData('BlaBlaBla');
    }

    public function testGetGeocodedDataWithRealPlace()
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new GeonamesProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['GEONAMES_USERNAME']);
        $result   = $provider->getGeocodedData('London');

        $this->assertEquals(51.50853, $result['latitude'], '', 0.01);
        $this->assertEquals(-0.12574, $result['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(51.15169, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(-0.70361, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(51.86537, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(0.45212, $result['bounds']['east'], '', 0.01);
        $this->assertEquals('London', $result['city']);
        $this->assertEquals('Greater London', $result['county']);
        $this->assertEquals('England', $result['region']);
        $this->assertEquals('United Kingdom', $result['country']);
        $this->assertEquals('GB', $result['countryCode']);
        $this->assertEquals('Europe/London', $result['timezone']);
    }

    public function testGetGeocodedDataWithRealPlaceWithLocale()
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new GeonamesProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['GEONAMES_USERNAME'], 'it_IT');
        $result   = $provider->getGeocodedData('London');

        $this->assertEquals(51.50853, $result['latitude'], '', 0.01);
        $this->assertEquals(-0.12574, $result['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(51.15169, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(-0.70361, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(51.86537, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(0.45212, $result['bounds']['east'], '', 0.01);
        $this->assertEquals('Londra', $result['city']);
        $this->assertEquals('Greater London', $result['county']);   // the webservice returns the same as default
        $this->assertEquals('Inghilterra', $result['region']);
        $this->assertEquals('Regno Unito', $result['country']);
        $this->assertEquals('GB', $result['countryCode']);
        $this->assertEquals('Europe/London', $result['timezone']);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new GeonamesProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['GEONAMES_USERNAME']);
        $result   = $provider->getReversedData(array(51.50853, -0.12574));

        $this->assertEquals(51.50853, $result['latitude'], '', 0.01);
        $this->assertEquals(-0.12574, $result['longitude'], '', 0.01);
        $this->assertEquals('London', $result['city']);
        $this->assertEquals('Greater London', $result['county']);
        $this->assertEquals('England', $result['region']);
        $this->assertEquals('United Kingdom', $result['country']);
        $this->assertEquals('GB', $result['countryCode']);
        $this->assertEquals('Europe/London', $result['timezone']);
    }

    public function testGetReversedDataWithRealCoordinatesWithLocale()
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new GeonamesProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['GEONAMES_USERNAME'], "it_IT");
        $result   = $provider->getReversedData(array(51.50853, -0.12574));

        $this->assertEquals(51.50853, $result['latitude'], '', 0.01);
        $this->assertEquals(-0.12574, $result['longitude'], '', 0.01);
        $this->assertEquals('Londra', $result['city']);
        $this->assertEquals('Greater London', $result['county']);
        $this->assertEquals('Inghilterra', $result['region']);
        $this->assertEquals('Regno Unito', $result['country']);
        $this->assertEquals('GB', $result['countryCode']);
        $this->assertEquals('Europe/London', $result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://api.geonames.org/findNearbyPlaceNameJSON?lat=-80.000000&lng=-170.000000&style=full&maxRows=1&username=username
     */
    public function testGetReversedDataWithBadCoordinates()
    {
        $badCoordinateResponse = <<<JSON
{
    "geonames": [ ]
}
JSON;
        $provider = new GeonamesProvider($this->getMockAdapterReturns($badCoordinateResponse), 'username');
        $provider->getReversedData(array(-80.000000, -170.000000));
    }
}
