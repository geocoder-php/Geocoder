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
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new TomTom($this->getMockAdapter($this->never()), null);
        $provider->geocode('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/geocoding/geocode?key=api_key&query=&maxResults=5".
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new TomTom($this->getMockAdapter(), 'api_key');
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/geocoding/geocode?key=api_key&query=&maxResults=5".
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new TomTom($this->getMockAdapter(), 'api_key');
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/geocoding/geocode?key=api_key&query=Tagensvej%2047%2C%202200%20K%C3%B8benhavn%20N&maxResults=5".
     */
    public function testGetGeocodedDataWithAddressContentReturnNull()
    {
        $provider = new TomTom($this->getMockAdapterReturns(null), 'api_key');
        $provider->geocode('Tagensvej 47, 2200 København N');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/geocoding/geocode?key=api_key&query=Tagensvej%2047%2C%202200%20K%C3%B8benhavn%20N&maxResults=5".
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new TomTom($this->getMockAdapter(), 'api_key');
        $provider->geocode('Tagensvej 47, 2200 København N');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/geocoding/geocode?key=api_key&query=foo&maxResults=5".
     */
    public function testGetGeocodedDataNoResult()
    {
        $noResult = <<<XML
<geoResponse duration="" debugInformation="" count="0" svnRevision="" version="" consolidatedMaps=""/>
XML;

        $provider = new TomTom($this->getMockAdapterReturns($noResult), 'api_key');
        $provider->geocode('foo');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['TOMTOM_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter(), $_SERVER['TOMTOM_GEOCODING_KEY']);
        $results  = $provider->geocode('Tagensvej 47, 2200 København N');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(55.704389, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(12.546129, $result->getLongitude(), '', 0.0001);
        $this->assertFalse($result->getBounds()->isDefined());
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Tagensvej', $result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Copenhagen', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getRegion()->getName());
        $this->assertNull($result->getRegion()->getCode());
        $this->assertEquals('Denmark', $result->getCountry()->getName());
        $this->assertEquals('DNK', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGetGeocodedDataWithRealAddressWithFrenchLocale()
    {
        if (!isset($_SERVER['TOMTOM_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter(), $_SERVER['TOMTOM_GEOCODING_KEY'], 'fr_FR');
        $results  = $provider->geocode('Tagensvej 47, 2200 København N');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(55.704389, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(12.546129, $result->getLongitude(), '', 0.0001);
        $this->assertFalse($result->getBounds()->isDefined());
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Tagensvej', $result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Copenhague', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getRegion()->getName());
        $this->assertNull($result->getRegion()->getCode());
        $this->assertEquals('Danemark', $result->getCountry()->getName());
        $this->assertEquals('DNK', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGetGeocodedDataWithRealAddressWithSwidishLocale()
    {
        if (!isset($_SERVER['TOMTOM_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter(), $_SERVER['TOMTOM_GEOCODING_KEY'], 'sv-SE');
        $results  = $provider->geocode('Tagensvej 47, 2200 København N');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(55.704389, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(12.546129, $result->getLongitude(), '', 0.0001);
        $this->assertFalse($result->getBounds()->isDefined());
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Tagensvej', $result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Köpenhamn', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getRegion()->getName());
        $this->assertNull($result->getRegion()->getCode());
        $this->assertEquals('Dania', $result->getCountry()->getName());
        $this->assertEquals('DNK', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGetGeocodedDataWithRealAddressReturnsMultipleResults()
    {
        if (!isset($_SERVER['TOMTOM_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter(), $_SERVER['TOMTOM_GEOCODING_KEY']);
        $results  = $provider->geocode('Paris');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.856898, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(2.350844, $result->getLongitude(), '', 0.0001);
        $this->assertFalse($result->getBounds()->isDefined());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertEquals('Ile-de-France', $result->getRegion()->getName());
        $this->assertNull($result->getRegion()->getCode());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FRA', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());

        /** @var \Geocoder\Model\Address $result */
        $result = $results[1];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(33.661426, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(-95.556321, $result->getLongitude(), '', 0.0001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('Texas', $result->getRegion()->getName());
        $this->assertEquals('United States',$result->getCountry()->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());

        /** @var \Geocoder\Model\Address $result */
        $result = $results[2];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(36.302754, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(-88.326359, $result->getLongitude(), '', 0.0001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('Tennessee', $result->getRegion()->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());

        /** @var \Geocoder\Model\Address $result */
        $result = $results[3];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(-19.039448, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(29.560445, $result->getLongitude(), '', 0.0001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('Midlands', $result->getRegion()->getName());
        $this->assertEquals('Zimbabwe', $result->getCountry()->getName());
        $this->assertEquals('ZWE', $result->getCountry()->getCode());

        /** @var \Geocoder\Model\Address $result */
        $result = $results[4];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(35.292105, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(-93.729922, $result->getLongitude(), '', 0.0001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('Arkansas', $result->getRegion()->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TomTom provider does not support IP addresses, only street addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new TomTom($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TomTom provider does not support IP addresses, only street addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new TomTom($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TomTom provider does not support IP addresses, only street addresses.
     */
    public function testGetGeocodedDataWithIPv4()
    {
        $provider = new TomTom($this->getAdapter(), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TomTom provider does not support IP addresses, only street addresses.
     */
    public function testGetGeocodedDataWithIPv6()
    {
        $provider = new TomTom($this->getAdapter(), 'api_key');
        $provider->geocode('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage No Map API Key provided
     */
    public function testGetReversedDataWithoutApiKey()
    {
        $provider = new TomTom($this->getMockAdapter($this->never()), null);
        $provider->reverse(1, 2);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/services/reverseGeocode/3/xml?key=api_key&point=1.000000,2.000000".
     */
    public function testGetReversedData()
    {
        $provider = new TomTom($this->getMockAdapter(), 'api_key');
        $provider->reverse(1, 2);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/services/reverseGeocode/3/xml?key=api_key&point=48.863216,2.388772".
     */
    public function testGetReversedDataWithCoordinatesContentReturnNull()
    {
        $provider = new TomTom($this->getMockAdapterReturns(null), 'api_key');
        $provider->reverse(48.86321648955345, 2.3887719959020615);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/services/reverseGeocode/3/xml?key=api_key&point=60.453947,22.256784".
     */
    public function testGetReversedDataWithCoordinatesGetsEmptyContent()
    {
        $provider = new TomTom($this->getMockAdapterReturns(''), 'api_key');
        $provider->reverse('60.4539471728726', '22.2567841926781');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "https://api.tomtom.com/lbs/services/reverseGeocode/3/xml?key=api_key&point=1.000000,2.000000".
     */
    public function testGetReversedDataError400()
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
    public function testGetReversedDataError403()
    {
        $error403 = <<<XML
<errorResponse version="" description="" errorCode="403"/>
XML;

        $provider = new TomTom($this->getMockAdapterReturns($error403), 'api_key');
        $provider->reverse(1, 2);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter(), $_SERVER['TOMTOM_MAP_KEY']);
        $results  = $provider->reverse(48.86321648955345, 2.3887719959020615);

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.86323, $result->getLatitude(), '', 0.001);
        $this->assertEquals(2.38877, $result->getLongitude(), '', 0.001);
        $this->assertFalse($result->getBounds()->isDefined());
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('20e Arrondissement Paris', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getRegion()->getName());
        $this->assertNull($result->getRegion()->getCode());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FRA', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGetGeocodedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter(),  $_SERVER['TOMTOM_MAP_KEY']);
        $results  = $provider->reverse(56.5231, 10.0659);

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(56.52435, $result->getLatitude(), '', 0.001);
        $this->assertEquals(10.06744, $result->getLongitude(), '', 0.001);
        $this->assertFalse($result->getBounds()->isDefined());
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Stabelsvej', $result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Spentrup', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getRegion()->getName());
        $this->assertNull($result->getRegion()->getCode());
        $this->assertEquals('Denmark', $result->getCountry()->getName());
        $this->assertEquals('DNK', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }
}
