<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\TomTomProvider;

class TomTomProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new TomTomProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('tomtom', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage No Geocoding API Key provided
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new TomTomProvider($this->getMockAdapter($this->never()), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query https://api.tomtom.com/lbs/geocoding/geocode?key=api_key&maxResults=1&query=
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new TomTomProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query https://api.tomtom.com/lbs/geocoding/geocode?key=api_key&maxResults=1&query=
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new TomTomProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query https://api.tomtom.com/lbs/geocoding/geocode?key=api_key&maxResults=1&query=Tagensvej%2047%2C%202200%20K%C3%B8benhavn%20N
     */
    public function testGetGeocodedDataWithAddressContentReturnNull()
    {
        $provider = new TomTomProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getGeocodedData('Tagensvej 47, 2200 København N');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query https://api.tomtom.com/lbs/geocoding/geocode?key=api_key&maxResults=1&query=Tagensvej%2047%2C%202200%20K%C3%B8benhavn%20N
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new TomTomProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('Tagensvej 47, 2200 København N');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query https://api.tomtom.com/lbs/geocoding/geocode?key=api_key&maxResults=1&query=foo
     */
    public function testGetGeocodedDataNoResult()
    {
        $noResult = <<<XML
<geoResponse duration="" debugInformation="" count="0" svnRevision="" version="" consolidatedMaps=""/>
XML;

        $provider = new TomTomProvider($this->getMockAdapterReturns($noResult), 'api_key');
        $provider->getGeocodedData('foo');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['TOMTOM_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new TomTomProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['TOMTOM_GEOCODING_KEY']);
        $result   = $provider->getGeocodedData('Tagensvej 47, 2200 København N');

        $this->assertEquals(55.704389, $result['latitude'], '', 0.0001);
        $this->assertEquals(12.546129, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Tagensvej', $result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertEquals('Copenhagen', $result['city']);
        $this->assertNull($result['cityDistrict']);
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

        $provider = new TomTomProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['TOMTOM_GEOCODING_KEY'], 'fr_FR');
        $result   = $provider->getGeocodedData('Tagensvej 47, 2200 København N');

        $this->assertEquals(55.704389, $result['latitude'], '', 0.0001);
        $this->assertEquals(12.546129, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Tagensvej', $result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertEquals('Copenhague', $result['city']);
        $this->assertNull($result['cityDistrict']);
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

        $provider = new TomTomProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['TOMTOM_GEOCODING_KEY'], 'sv-SE');
        $result   = $provider->getGeocodedData('Tagensvej 47, 2200 København N');

        $this->assertEquals(55.704389, $result['latitude'], '', 0.0001);
        $this->assertEquals(12.546129, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Tagensvej', $result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertEquals('Köpenhamn', $result['city']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('Dania', $result['country']);
        $this->assertEquals('DNK', $result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The TomTomProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new TomTomProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The TomTomProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new TomTomProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The TomTomProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv4()
    {
        $provider = new TomTomProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The TomTomProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv6()
    {
        $provider = new TomTomProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'api_key');
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage No Map API Key provided
     */
    public function testGetReversedDataWithoutApiKey()
    {
        $provider = new TomTomProvider($this->getMockAdapter($this->never()), null);
        $provider->getReversedData(array(1, 2));
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query https://api.tomtom.com/lbs/services/reverseGeocode/3/xml?key=api_key&point=1.000000,2.000000
     */
    public function testGetReversedData()
    {
        $provider = new TomTomProvider($this->getMockAdapter(), 'api_key');
        $provider->getReversedData(array(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query https://api.tomtom.com/lbs/services/reverseGeocode/3/xml?key=api_key&point=48.863216,2.388772
     */
    public function testGetReversedDataWithCoordinatesContentReturnNull()
    {
        $provider = new TomTomProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getReversedData(array(48.86321648955345, 2.3887719959020615));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query https://api.tomtom.com/lbs/services/reverseGeocode/3/xml?key=api_key&point=60.453947,22.256784
     */
    public function testGetReversedDataWithCoordinatesGetsEmptyContent()
    {
        $provider = new TomTomProvider($this->getMockAdapterReturns(''), 'api_key');
        $provider->getReversedData(array('60.4539471728726', '22.2567841926781'));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query https://api.tomtom.com/lbs/services/reverseGeocode/3/xml?key=api_key&point=1.000000,2.000000
     */
    public function testGetReversedDataError400()
    {
        $error400 = <<<XML
<errorResponse version="" description="" errorCode="400"/>
XML;

        $provider = new TomTomProvider($this->getMockAdapterReturns($error400), 'api_key');
        $provider->getReversedData(array(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage Map API Key provided is not valid.
     */
    public function testGetReversedDataError403()
    {
        $error403 = <<<XML
<errorResponse version="" description="" errorCode="403"/>
XML;

        $provider = new TomTomProvider($this->getMockAdapterReturns($error403), 'api_key');
        $provider->getReversedData(array(1, 2));
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTomProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['TOMTOM_MAP_KEY']);
        $result = $provider->getReversedData(array(48.86321648955345, 2.3887719959020615));

        $this->assertEquals(48.86323, $result['latitude'], '', 0.001);
        $this->assertEquals(2.38877, $result['longitude'], '', 0.001);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Avenue Gambetta', $result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertEquals('20ème Arrondissement Paris', $result['city']);
        $this->assertNull($result['cityDistrict']);
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

        $provider = new TomTomProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(),  $_SERVER['TOMTOM_MAP_KEY']);
        $result = $provider->getReversedData(array(56.5231, 10.0659));

        $this->assertEquals(56.52435, $result['latitude'], '', 0.001);
        $this->assertEquals(10.06744, $result['longitude'], '', 0.001);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Stabelsvej', $result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertEquals('Spentrup', $result['city']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('Denmark', $result['country']);
        $this->assertEquals('DNK', $result['countryCode']);
        $this->assertNull($result['timezone']);
    }
}
