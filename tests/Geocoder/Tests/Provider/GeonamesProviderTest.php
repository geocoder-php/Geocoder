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
     * @expectedExceptionMessage Could not execute query http://api.geonames.org/searchJSON?q=&maxRows=5&style=full&username=username
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new GeonamesProvider($this->getMockAdapter(), 'username');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage No places found for query http://api.geonames.org/searchJSON?q=BlaBlaBla&maxRows=5&style=full&username=username
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

        $provider = new GeonamesProvider($this->getAdapter(), $_SERVER['GEONAMES_USERNAME']);
        $results  = $provider->getGeocodedData('London');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(51.508528775863, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(-0.12574195861816, $results[0]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[0]['bounds']);
        $this->assertArrayHasKey('west', $results[0]['bounds']);
        $this->assertArrayHasKey('north', $results[0]['bounds']);
        $this->assertArrayHasKey('east', $results[0]['bounds']);
        $this->assertEquals(51.151689398345, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(-0.70360885396019, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(51.865368153381, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(0.45212493672386, $results[0]['bounds']['east'], '', 0.01);
        $this->assertEquals('London', $results[0]['city']);
        $this->assertEquals('Greater London', $results[0]['county']);
        $this->assertEquals('England', $results[0]['region']);
        $this->assertEquals('United Kingdom', $results[0]['country']);
        $this->assertEquals('GB', $results[0]['countryCode']);
        $this->assertEquals('Europe/London', $results[0]['timezone']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(-33.015285093464, $results[1]['latitude'], '', 0.01);
        $this->assertEquals(27.911624908447, $results[1]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[1]['bounds']);
        $this->assertArrayHasKey('west', $results[1]['bounds']);
        $this->assertArrayHasKey('north', $results[1]['bounds']);
        $this->assertArrayHasKey('east', $results[1]['bounds']);
        $this->assertEquals(-33.104996458003, $results[1]['bounds']['south'], '', 0.01);
        $this->assertEquals(27.804746435655, $results[1]['bounds']['west'], '', 0.01);
        $this->assertEquals(-32.925573728925, $results[1]['bounds']['north'], '', 0.01);
        $this->assertEquals(28.018503381239, $results[1]['bounds']['east'], '', 0.01);
        $this->assertEquals('East London', $results[1]['city']);
        $this->assertEquals('Buffalo City Metropolitan Municipality', $results[1]['county']);
        $this->assertEquals('Eastern Cape', $results[1]['region']);
        $this->assertEquals('South Africa', $results[1]['country']);
        $this->assertEquals('ZA', $results[1]['countryCode']);
        $this->assertEquals('Africa/Johannesburg', $results[1]['timezone']);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(51.512788890295, $results[2]['latitude'], '', 0.01);
        $this->assertEquals(-0.091838836669922, $results[2]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[2]['bounds']);
        $this->assertArrayHasKey('west', $results[2]['bounds']);
        $this->assertArrayHasKey('north', $results[2]['bounds']);
        $this->assertArrayHasKey('east', $results[2]['bounds']);
        $this->assertEquals(51.155949512764, $results[2]['bounds']['south'], '', 0.01);
        $this->assertEquals(-0.66976046752962, $results[2]['bounds']['west'], '', 0.01);
        $this->assertEquals(51.869628267826, $results[2]['bounds']['north'], '', 0.01);
        $this->assertEquals(0.48608279418978, $results[2]['bounds']['east'], '', 0.01);
        $this->assertEquals('City of London', $results[2]['city']);
        $this->assertEquals('Greater London', $results[2]['county']);
        $this->assertEquals('England', $results[2]['region']);
        $this->assertEquals('United Kingdom', $results[2]['country']);
        $this->assertEquals('GB', $results[2]['countryCode']);
        $this->assertEquals('Europe/London', $results[2]['timezone']);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(42.983389283, $results[3]['latitude'], '', 0.01);
        $this->assertEquals(-81.233042387, $results[3]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[3]['bounds']);
        $this->assertArrayHasKey('west', $results[3]['bounds']);
        $this->assertArrayHasKey('north', $results[3]['bounds']);
        $this->assertArrayHasKey('east', $results[3]['bounds']);
        $this->assertEquals(42.907075642763, $results[3]['bounds']['south'], '', 0.01);
        $this->assertEquals(-81.337489676463, $results[3]['bounds']['west'], '', 0.01);
        $this->assertEquals(43.059702923237, $results[3]['bounds']['north'], '', 0.01);
        $this->assertEquals(-81.128595097537, $results[3]['bounds']['east'], '', 0.01);
        $this->assertEquals('London', $results[3]['city']);
        $this->assertEquals('', $results[3]['county']);
        $this->assertEquals('Ontario', $results[3]['region']);
        $this->assertEquals('Canada', $results[3]['country']);
        $this->assertEquals('CA', $results[3]['countryCode']);
        $this->assertEquals('America/Toronto', $results[3]['timezone']);

        $this->assertInternalType('array', $results[4]);
        $this->assertEquals(41.3556539, $results[4]['latitude'], '', 0.01);
        $this->assertEquals(-72.0995209, $results[4]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[4]['bounds']);
        $this->assertArrayHasKey('west', $results[4]['bounds']);
        $this->assertArrayHasKey('north', $results[4]['bounds']);
        $this->assertArrayHasKey('east', $results[4]['bounds']);
        $this->assertEquals(41.334087887904, $results[4]['bounds']['south'], '', 0.01);
        $this->assertEquals(-72.128261254846, $results[4]['bounds']['west'], '', 0.01);
        $this->assertEquals(41.377219912096, $results[4]['bounds']['north'], '', 0.01);
        $this->assertEquals(-72.070780545154, $results[4]['bounds']['east'], '', 0.01);
        $this->assertEquals('New London', $results[4]['city']);
        $this->assertEquals('New London County', $results[4]['county']);
        $this->assertEquals('Connecticut', $results[4]['region']);
        $this->assertEquals('United States', $results[4]['country']);
        $this->assertEquals('US', $results[4]['countryCode']);
        $this->assertEquals('America/New_York', $results[4]['timezone']);
    }

    public function testGetGeocodedDataWithRealPlaceWithLocale()
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new GeonamesProvider($this->getAdapter(), $_SERVER['GEONAMES_USERNAME'], 'it_IT');
        $results  = $provider->getGeocodedData('London');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(51.50853, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(-0.12574, $results[0]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[0]['bounds']);
        $this->assertArrayHasKey('west', $results[0]['bounds']);
        $this->assertArrayHasKey('north', $results[0]['bounds']);
        $this->assertArrayHasKey('east', $results[0]['bounds']);
        $this->assertEquals(51.15169, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(-0.70361, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(51.86537, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(0.45212, $results[0]['bounds']['east'], '', 0.01);
        $this->assertEquals('Londra', $results[0]['city']);
        $this->assertEquals('Greater London', $results[0]['county']);
        $this->assertEquals('Inghilterra', $results[0]['region']);
        $this->assertEquals('Regno Unito', $results[0]['country']);
        $this->assertEquals('GB', $results[0]['countryCode']);
        $this->assertEquals('Europe/London', $results[0]['timezone']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(-33.015285093464, $results[1]['latitude'], '', 0.01);
        $this->assertEquals(27.911624908447, $results[1]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[1]['bounds']);
        $this->assertArrayHasKey('west', $results[1]['bounds']);
        $this->assertArrayHasKey('north', $results[1]['bounds']);
        $this->assertArrayHasKey('east', $results[1]['bounds']);
        $this->assertEquals(-33.104996458003, $results[1]['bounds']['south'], '', 0.01);
        $this->assertEquals(27.804746435655, $results[1]['bounds']['west'], '', 0.01);
        $this->assertEquals(-32.925573728925, $results[1]['bounds']['north'], '', 0.01);
        $this->assertEquals(28.018503381239, $results[1]['bounds']['east'], '', 0.01);
        $this->assertEquals('East London', $results[1]['city']);
        $this->assertEquals('Buffalo City Metropolitan Municipality', $results[1]['county']);
        $this->assertEquals('Eastern Cape', $results[1]['region']);
        $this->assertEquals('Sudafrica', $results[1]['country']);
        $this->assertEquals('ZA', $results[1]['countryCode']);
        $this->assertEquals('Africa/Johannesburg', $results[1]['timezone']);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(51.512788890295, $results[2]['latitude'], '', 0.01);
        $this->assertEquals(-0.091838836669922, $results[2]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[2]['bounds']);
        $this->assertArrayHasKey('west', $results[2]['bounds']);
        $this->assertArrayHasKey('north', $results[2]['bounds']);
        $this->assertArrayHasKey('east', $results[2]['bounds']);
        $this->assertEquals(51.155949512764, $results[2]['bounds']['south'], '', 0.01);
        $this->assertEquals(-0.66976046752962, $results[2]['bounds']['west'], '', 0.01);
        $this->assertEquals(51.869628267826, $results[2]['bounds']['north'], '', 0.01);
        $this->assertEquals(0.48608279418978, $results[2]['bounds']['east'], '', 0.01);
        $this->assertEquals('Città di Londra', $results[2]['city']);
        $this->assertEquals('Greater London', $results[2]['county']);
        $this->assertEquals('Inghilterra', $results[2]['region']);
        $this->assertEquals('Regno Unito', $results[2]['country']);
        $this->assertEquals('GB', $results[2]['countryCode']);
        $this->assertEquals('Europe/London', $results[2]['timezone']);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(42.983389283, $results[3]['latitude'], '', 0.01);
        $this->assertEquals(-81.233042387, $results[3]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[3]['bounds']);
        $this->assertArrayHasKey('west', $results[3]['bounds']);
        $this->assertArrayHasKey('north', $results[3]['bounds']);
        $this->assertArrayHasKey('east', $results[3]['bounds']);
        $this->assertEquals(42.907075642763, $results[3]['bounds']['south'], '', 0.01);
        $this->assertEquals(-81.337489676463, $results[3]['bounds']['west'], '', 0.01);
        $this->assertEquals(43.059702923237, $results[3]['bounds']['north'], '', 0.01);
        $this->assertEquals(-81.128595097537, $results[3]['bounds']['east'], '', 0.01);
        $this->assertEquals('London', $results[3]['city']);
        $this->assertEquals('', $results[3]['county']);
        $this->assertEquals('Ontario', $results[3]['region']);
        $this->assertEquals('Canada', $results[3]['country']);
        $this->assertEquals('CA', $results[3]['countryCode']);
        $this->assertEquals('America/Toronto', $results[3]['timezone']);

        $this->assertInternalType('array', $results[4]);
        $this->assertEquals(41.3556539, $results[4]['latitude'], '', 0.01);
        $this->assertEquals(-72.0995209, $results[4]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[4]['bounds']);
        $this->assertArrayHasKey('west', $results[4]['bounds']);
        $this->assertArrayHasKey('north', $results[4]['bounds']);
        $this->assertArrayHasKey('east', $results[4]['bounds']);
        $this->assertEquals(41.334087887904, $results[4]['bounds']['south'], '', 0.01);
        $this->assertEquals(-72.128261254846, $results[4]['bounds']['west'], '', 0.01);
        $this->assertEquals(41.377219912096, $results[4]['bounds']['north'], '', 0.01);
        $this->assertEquals(-72.070780545154, $results[4]['bounds']['east'], '', 0.01);
        $this->assertEquals('New London', $results[4]['city']);
        $this->assertEquals('Contea di New London', $results[4]['county']);
        $this->assertEquals('Connecticut', $results[4]['region']);
        $this->assertEquals('Stati Uniti', $results[4]['country']);
        $this->assertEquals('US', $results[4]['countryCode']);
        $this->assertEquals('America/New_York', $results[4]['timezone']);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new GeonamesProvider($this->getAdapter(), $_SERVER['GEONAMES_USERNAME']);
        $results  = $provider->getReversedData(array(51.50853, -0.12574));

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
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

        $provider = new GeonamesProvider($this->getAdapter(), $_SERVER['GEONAMES_USERNAME'], 'it_IT');
        $results  = $provider->getReversedData(array(51.50853, -0.12574));

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
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
     * @expectedExceptionMessage Could not execute query http://api.geonames.org/findNearbyPlaceNameJSON?lat=-80.000000&lng=-170.000000&style=full&maxRows=5&username=username
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
