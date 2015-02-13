<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\TomTom;

class TomTomTest extends TestCase
{
    public function testGetName()
    {
        $provider = new TomTom($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('tomtom', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage No API Key provided.
     */
    public function testGeocodeWithNullApiKey()
    {
        $provider = new TomTom($this->getMockAdapter($this->never()), null);
        $provider->geocode('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/services/geocode/4/geocode?key=api_key&query=&maxResults=5".
     */
    public function testGeocodeWithNull()
    {
        $provider = new TomTom($this->getMockAdapter(), 'api_key');
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/services/geocode/4/geocode?key=api_key&query=&maxResults=5".
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new TomTom($this->getMockAdapter(), 'api_key');
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/services/geocode/4/geocode?key=api_key&query=Tagensvej%2047%2C%202200%20K%C3%B8benhavn%20N&maxResults=5".
     */
    public function testGeocodeWithAddressContentReturnNull()
    {
        $provider = new TomTom($this->getMockAdapterReturns(null), 'api_key');
        $provider->geocode('Tagensvej 47, 2200 København N');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/services/geocode/4/geocode?key=api_key&query=Tagensvej%2047%2C%202200%20K%C3%B8benhavn%20N&maxResults=5".
     */
    public function testGeocodeWithAddress()
    {
        $provider = new TomTom($this->getMockAdapter(), 'api_key');
        $provider->geocode('Tagensvej 47, 2200 København N');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/services/geocode/4/geocode?key=api_key&query=foo&maxResults=5".
     */
    public function testGeocodeNoResult()
    {
        $noResult = <<<XML
<geoResponse duration="" debugInformation="" count="0" svnRevision="" version="" consolidatedMaps=""/>
XML;

        $provider = new TomTom($this->getMockAdapterReturns($noResult), 'api_key');
        $provider->geocode('foo');
    }

    public function testGeocodeWithRealAddress()
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results  = $provider->geocode('Tagensvej 47, 2200 København N');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(55.704389, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(12.546129, $result->getLongitude(), '', 0.0001);
        $this->assertFalse($result->getBounds()->isDefined());
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Tagensvej', $result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Copenhagen', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(0, $result->getAdminLevels());
        $this->assertEquals('Denmark', $result->getCountry()->getName());
        $this->assertEquals('DNK', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealAddressWithFrenchLocale()
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY'], 'fr_FR');
        $results  = $provider->geocode('Tagensvej 47, 2200 København N');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(55.704389, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(12.546129, $result->getLongitude(), '', 0.0001);
        $this->assertFalse($result->getBounds()->isDefined());
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Tagensvej', $result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Copenhague', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(0, $result->getAdminLevels());
        $this->assertEquals('Danemark', $result->getCountry()->getName());
        $this->assertEquals('DNK', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealAddressWithSwedishLocale()
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY'], 'sv-SE');
        $results  = $provider->geocode('Tagensvej 47, 2200 København N');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(55.704389, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(12.546129, $result->getLongitude(), '', 0.0001);
        $this->assertFalse($result->getBounds()->isDefined());
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Tagensvej', $result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Köpenhamn', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(0, $result->getAdminLevels());
        $this->assertEquals('Danmark', $result->getCountry()->getName());
        $this->assertEquals('DNK', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealAddressReturnsMultipleResults()
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results  = $provider->geocode('Paris');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.856898, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(2.350844, $result->getLongitude(), '', 0.0001);
        $this->assertFalse($result->getBounds()->isDefined());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Ile-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FRA', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(33.661426, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(-95.556321, $result->getLongitude(), '', 0.0001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Texas', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States',$result->getCountry()->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(36.302754, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(-88.326359, $result->getLongitude(), '', 0.0001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Tennessee', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(3);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(-19.039448, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(29.560445, $result->getLongitude(), '', 0.0001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Midlands', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Zimbabwe', $result->getCountry()->getName());
        $this->assertEquals('ZWE', $result->getCountry()->getCode());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(4);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(35.292105, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(-93.729922, $result->getLongitude(), '', 0.0001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Arkansas', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TomTom provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new TomTom($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TomTom provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new TomTom($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TomTom provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithIPv4()
    {
        $provider = new TomTom($this->getAdapter(), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TomTom provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithIPv6()
    {
        $provider = new TomTom($this->getAdapter(), 'api_key');
        $provider->geocode('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage No Map API Key provided
     */
    public function testReverseWithoutApiKey()
    {
        $provider = new TomTom($this->getMockAdapter($this->never()), null);
        $provider->reverse(1, 2);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/services/reverseGeocode/3/xml?key=api_key&point=1.000000,2.000000".
     */
    public function testReverse()
    {
        $provider = new TomTom($this->getMockAdapter(), 'api_key');
        $provider->reverse(1, 2);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/services/reverseGeocode/3/xml?key=api_key&point=48.863216,2.388772".
     */
    public function testReverseWithCoordinatesContentReturnNull()
    {
        $provider = new TomTom($this->getMockAdapterReturns(null), 'api_key');
        $provider->reverse(48.86321648955345, 2.3887719959020615);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/services/reverseGeocode/3/xml?key=api_key&point=60.453947,22.256784".
     */
    public function testReverseWithCoordinatesGetsEmptyContent()
    {
        $provider = new TomTom($this->getMockAdapterReturns(''), 'api_key');
        $provider->reverse('60.4539471728726', '22.2567841926781');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/services/reverseGeocode/3/xml?key=api_key&point=1.000000,2.000000".
     */
    public function testReverseError400()
    {
        $error400 = <<<XML
<errorResponse version="" description="" errorCode="400"/>
XML;

        $provider = new TomTom($this->getMockAdapterReturns($error400), 'api_key');
        $provider->reverse(1, 2);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage Map API Key provided is not valid.
     */
    public function testReverseError403()
    {
        $error403 = <<<XML
<errorResponse version="" description="" errorCode="403"/>
XML;

        $provider = new TomTom($this->getMockAdapterReturns($error403), 'api_key');
        $provider->reverse(1, 2);
    }

    public function testReverseWithRealCoordinates()
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results  = $provider->reverse(48.86321648955345, 2.3887719959020615);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.86323, $result->getLatitude(), '', 0.001);
        $this->assertEquals(2.38877, $result->getLongitude(), '', 0.001);
        $this->assertFalse($result->getBounds()->isDefined());
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('20e Arrondissement Paris', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(0, $result->getAdminLevels());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FRA', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealCoordinates()
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter($_SERVER['TOMTOM_MAP_KEY']),  $_SERVER['TOMTOM_MAP_KEY']);
        $results  = $provider->reverse(56.5231, 10.0659);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(56.52435, $result->getLatitude(), '', 0.001);
        $this->assertEquals(10.06744, $result->getLongitude(), '', 0.001);
        $this->assertFalse($result->getBounds()->isDefined());
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Stabelsvej', $result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Spentrup', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(0, $result->getAdminLevels());
        $this->assertEquals('Denmark', $result->getCountry()->getName());
        $this->assertEquals('DNK', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }
}
