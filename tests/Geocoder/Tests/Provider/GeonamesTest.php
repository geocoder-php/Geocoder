<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\Geonames;

class GeonamesTest extends TestCase
{
    public function testGetName()
    {
        $provider = new Geonames($this->getMockAdapter($this->never()), 'username');
        $this->assertEquals('geonames', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage No username provided.
     */
    public function testGeocodeWithNullUsername()
    {
        $provider = new Geonames($this->getMock('\Ivory\HttpAdapter\HttpAdapterInterface'), null);
        $provider->geocode('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage No username provided.
     */
    public function testReverseWithNullUsername()
    {
        $provider = new Geonames($this->getMock('\Ivory\HttpAdapter\HttpAdapterInterface'), null);
        $provider->reverse(1,2);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geonames provider does not support IP addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new Geonames($this->getMockAdapter($this->never()), 'username');
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geonames provider does not support IP addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new Geonames($this->getMockAdapter($this->never()), 'username');
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://api.geonames.org/searchJSON?q=&maxRows=5&style=full&username=username".
     */
    public function testGeocodeWithNull()
    {
        $provider = new Geonames($this->getMockAdapter(), 'username');
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage No places found for query "http://api.geonames.org/searchJSON?q=BlaBlaBla&maxRows=5&style=full&username=username".
     */
    public function testGeocodeWithUnknownCity()
    {
        $noPlacesFoundResponse = <<<JSON
{
    "totalResultsCount": 0,
    "geonames": [ ]
}
JSON;
        $provider = new Geonames($this->getMockAdapterReturns($noPlacesFoundResponse), 'username');
        $provider->geocode('BlaBlaBla');
    }

    public function testGeocodeWithRealPlace()
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new Geonames($this->getAdapter($_SERVER['GEONAMES_USERNAME']), $_SERVER['GEONAMES_USERNAME']);
        $results  = $provider->geocode('London');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(51.508528775863, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-0.12574195861816, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(51.151689398345, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(-0.70360885396019, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(51.865368153381, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(0.45212493672386, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals('London', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Greater London', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('England', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountryCode());
        $this->assertEquals('Europe/London', $result->getTimezone());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(-33.015285093464, $result->getLatitude(), '', 0.01);
        $this->assertEquals(27.911624908447, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(-33.104996458003, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(27.804746435655, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(-32.925573728925, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(28.018503381239, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals('East London', $result->getLocality());
        $this->assertCount(3, $result->getAdminLevels());
        $this->assertEquals('Buffalo City', $result->getAdminLevels()->get(3)->getName());
        $this->assertEquals('Buffalo City Metropolitan Municipality', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Eastern Cape', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('South Africa', $result->getCountry()->getName());
        $this->assertEquals('ZA', $result->getCountryCode());
        $this->assertEquals('Africa/Johannesburg', $result->getTimezone());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(51.512788890295, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-0.091838836669922, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(51.155949512764, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(-0.66976046752962, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(51.869628267826, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(0.48608279418978, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals('City of London', $result->getLocality());
        $this->assertCount(3, $result->getAdminLevels());
        $this->assertEquals('City of London', $result->getAdminLevels()->get(3)->getName());
        $this->assertEquals('Greater London', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('England', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountryCode());
        $this->assertEquals('Europe/London', $result->getTimezone());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(3);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(42.983389283, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-81.233042387, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(42.907075642763, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(-81.337489676463, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(43.059702923237, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(-81.128595097537, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals('London', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Ontario', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Canada', $result->getCountry()->getName());
        $this->assertEquals('CA', $result->getCountryCode());
        $this->assertEquals('America/Toronto', $result->getTimezone());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(4);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(41.3556539, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-72.0995209, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(41.334087887904, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(-72.128261254846, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(41.377219912096, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(-72.070780545154, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals('New London', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('New London County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Connecticut', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountryCode());
        $this->assertEquals('America/New_York', $result->getTimezone());
    }

    public function testGeocodeWithRealPlaceWithLocale()
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new Geonames($this->getAdapter($_SERVER['GEONAMES_USERNAME']), $_SERVER['GEONAMES_USERNAME'], 'it_IT');
        $results  = $provider->geocode('London');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(51.50853, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-0.12574, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(51.15169, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(-0.70361, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(51.86537, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(0.45212, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals('Londra', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Greater London', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Inghilterra', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Regno Unito', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountryCode());
        $this->assertEquals('Europe/London', $result->getTimezone());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(-33.015285093464, $result->getLatitude(), '', 0.01);
        $this->assertEquals(27.911624908447, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(-33.104996458003, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(27.804746435655, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(-32.925573728925, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(28.018503381239, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals('East London', $result->getLocality());
        $this->assertCount(3, $result->getAdminLevels());
        $this->assertEquals('Buffalo City', $result->getAdminLevels()->get(3)->getName());
        $this->assertEquals('Buffalo City Metropolitan Municipality', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Eastern Cape', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Sudafrica', $result->getCountry()->getName());
        $this->assertEquals('ZA', $result->getCountryCode());
        $this->assertEquals('Africa/Johannesburg', $result->getTimezone());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(51.512788890295, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-0.091838836669922, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(51.155949512764, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(-0.66976046752962, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(51.869628267826, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(0.48608279418978, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals('Città di Londra', $result->getLocality());
        $this->assertCount(3, $result->getAdminLevels());
        $this->assertEquals('City of London', $result->getAdminLevels()->get(3)->getName());
        $this->assertEquals('Greater London', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Inghilterra', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Regno Unito', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountryCode());
        $this->assertEquals('Europe/London', $result->getTimezone());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(3);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(42.983389283, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-81.233042387, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(42.907075642763, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(-81.337489676463, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(43.059702923237, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(-81.128595097537, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals('London', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Ontario', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Canada', $result->getCountry()->getName());
        $this->assertEquals('CA', $result->getCountryCode());
        $this->assertEquals('America/Toronto', $result->getTimezone());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(4);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(41.3556539, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-72.0995209, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(41.334087887904, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(-72.128261254846, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(41.377219912096, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(-72.070780545154, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals('New London', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Contea di New London', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Connecticut', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Stati Uniti', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountryCode());
        $this->assertEquals('America/New_York', $result->getTimezone());
    }

    public function testReverseWithRealCoordinates()
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new Geonames($this->getAdapter($_SERVER['GEONAMES_USERNAME']), $_SERVER['GEONAMES_USERNAME']);
        $results  = $provider->reverse(51.50853, -0.12574);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(51.50853, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-0.12574, $result->getLongitude(), '', 0.01);
        $this->assertEquals('London', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Greater London', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('England', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountryCode());
        $this->assertEquals('Europe/London', $result->getTimezone());
    }

    public function testReverseWithRealCoordinatesWithLocale()
    {
        if (!isset($_SERVER['GEONAMES_USERNAME'])) {
            $this->markTestSkipped('You need to configure the GEONAMES_USERNAME value in phpunit.xml');
        }

        $provider = new Geonames($this->getAdapter($_SERVER['GEONAMES_USERNAME']), $_SERVER['GEONAMES_USERNAME'], 'it_IT');
        $results  = $provider->reverse(51.50853, -0.12574);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(51.50853, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-0.12574, $result->getLongitude(), '', 0.01);
        $this->assertEquals('Londra', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Greater London', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Inghilterra', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Regno Unito', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountryCode());
        $this->assertEquals('Europe/London', $result->getTimezone());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://api.geonames.org/findNearbyPlaceNameJSON?lat=-80.000000&lng=-170.000000&style=full&maxRows=5&username=username".
     */
    public function testReverseWithBadCoordinates()
    {
        $badCoordinateResponse = <<<JSON
{
    "geonames": [ ]
}
JSON;
        $provider = new Geonames($this->getMockAdapterReturns($badCoordinateResponse), 'username');
        $provider->reverse(-80.000000, -170.000000);
    }
}
