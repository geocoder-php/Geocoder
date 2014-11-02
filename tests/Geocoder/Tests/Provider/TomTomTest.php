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
        $result   = $provider->geocode('Tagensvej 47, 2200 København N');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(55.704389, $result['latitude'], '', 0.0001);
        $this->assertEquals(12.546129, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Tagensvej', $result['streetName']);
        $this->assertNull($result['postalCode']);
        $this->assertEquals('Copenhagen', $result['locality']);
        $this->assertNull($result['subLocality']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('Denmark', $result['country']);
        $this->assertEquals('DNK', $result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressWithFrenchLocale()
    {
        if (!isset($_SERVER['TOMTOM_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter(), $_SERVER['TOMTOM_GEOCODING_KEY'], 'fr_FR');
        $result   = $provider->geocode('Tagensvej 47, 2200 København N');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(55.704389, $result['latitude'], '', 0.0001);
        $this->assertEquals(12.546129, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Tagensvej', $result['streetName']);
        $this->assertNull($result['postalCode']);
        $this->assertEquals('Copenhague', $result['locality']);
        $this->assertNull($result['subLocality']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('Danemark', $result['country']);
        $this->assertEquals('DNK', $result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressWithSwidishLocale()
    {
        if (!isset($_SERVER['TOMTOM_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter(), $_SERVER['TOMTOM_GEOCODING_KEY'], 'sv-SE');
        $result   = $provider->geocode('Tagensvej 47, 2200 København N');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(55.704389, $result['latitude'], '', 0.0001);
        $this->assertEquals(12.546129, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Tagensvej', $result['streetName']);
        $this->assertNull($result['postalCode']);
        $this->assertEquals('Köpenhamn', $result['locality']);
        $this->assertNull($result['subLocality']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('Dania', $result['country']);
        $this->assertEquals('DNK', $result['countryCode']);
        $this->assertNull($result['timezone']);
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

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(48.856898, $results[0]['latitude'], '', 0.0001);
        $this->assertEquals(2.350844, $results[0]['longitude'], '', 0.0001);
        $this->assertNull($results[0]['bounds']);
        $this->assertNull($results[0]['streetNumber']);
        $this->assertNull($results[0]['streetName']);
        $this->assertNull($results[0]['postalCode']);
        $this->assertEquals('Paris', $results[0]['locality']);
        $this->assertNull($results[0]['subLocality']);
        $this->assertEquals('Ile-de-France', $results[0]['region']);
        $this->assertNull($results[0]['regionCode']);
        $this->assertEquals('France', $results[0]['country']);
        $this->assertEquals('FRA', $results[0]['countryCode']);
        $this->assertNull($results[0]['timezone']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(33.661426, $results[1]['latitude'], '', 0.0001);
        $this->assertEquals(-95.556321, $results[1]['longitude'], '', 0.0001);
        $this->assertEquals('Paris', $results[1]['locality']);
        $this->assertEquals('Texas', $results[1]['region']);
        $this->assertEquals('United States', $results[1]['country']);
        $this->assertEquals('USA', $results[1]['countryCode']);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(36.302754, $results[2]['latitude'], '', 0.0001);
        $this->assertEquals(-88.326359, $results[2]['longitude'], '', 0.0001);
        $this->assertEquals('Paris', $results[2]['locality']);
        $this->assertEquals('Tennessee', $results[2]['region']);
        $this->assertEquals('United States', $results[2]['country']);
        $this->assertEquals('USA', $results[2]['countryCode']);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(-19.039448, $results[3]['latitude'], '', 0.0001);
        $this->assertEquals(29.560445, $results[3]['longitude'], '', 0.0001);
        $this->assertEquals('Paris', $results[3]['locality']);
        $this->assertEquals('Midlands', $results[3]['region']);
        $this->assertEquals('Zimbabwe', $results[3]['country']);
        $this->assertEquals('ZWE', $results[3]['countryCode']);

        $this->assertInternalType('array', $results[4]);
        $this->assertEquals(35.292105, $results[4]['latitude'], '', 0.0001);
        $this->assertEquals(-93.729922, $results[4]['longitude'], '', 0.0001);
        $this->assertEquals('Paris', $results[4]['locality']);
        $this->assertEquals('Arkansas', $results[4]['region']);
        $this->assertEquals('United States', $results[4]['country']);
        $this->assertEquals('USA', $results[4]['countryCode']);
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
     * @expectedException Geocoder\Exception\NoResult
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
        $result   = $provider->reverse(array(48.86321648955345, 2.3887719959020615));

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(48.86323, $result['latitude'], '', 0.001);
        $this->assertEquals(2.38877, $result['longitude'], '', 0.001);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Avenue Gambetta', $result['streetName']);
        $this->assertNull($result['postalCode']);
        $this->assertEquals('20e Arrondissement Paris', $result['locality']);
        $this->assertNull($result['subLocality']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FRA', $result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter(),  $_SERVER['TOMTOM_MAP_KEY']);
        $result   = $provider->reverse(array(56.5231, 10.0659));

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(56.52435, $result['latitude'], '', 0.001);
        $this->assertEquals(10.06744, $result['longitude'], '', 0.001);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Stabelsvej', $result['streetName']);
        $this->assertNull($result['postalCode']);
        $this->assertEquals('Spentrup', $result['locality']);
        $this->assertNull($result['subLocality']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('Denmark', $result['country']);
        $this->assertEquals('DNK', $result['countryCode']);
        $this->assertNull($result['timezone']);
    }
}
