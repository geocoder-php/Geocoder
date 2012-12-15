<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\OpenStreetMapsProvider;

class OpenStreetMapsProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new OpenStreetMapsProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('openstreetmaps', $provider->getName());
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('Läntinen Pitkäkatu 35, Turku');

        $this->assertEquals(60.4539471768582, $result['latitude'], '', 0.0001);
        $this->assertEquals(22.2567842183875, $result['longitude'], '', 0.0001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(60.4537582397461, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(22.2563400268555, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(60.4541320800781, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(22.2572231292725, $result['bounds']['east'], '', 0.0001);
        $this->assertEquals('20100', $result['zipcode']);
        $this->assertEquals(35, $result['streetNumber']);
        $this->assertEquals('Läntinen Pitkäkatu', $result['streetName']);
        $this->assertEquals('Turku', $result['city']);
        $this->assertEquals('VII', $result['cityDistrict']);
        //$this->assertEquals('Finland Proper', $result['county']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        //$this->assertEquals('Finland', $result['country']);
        $this->assertEquals('FI', $result['countryCode']);
    }

    public function testGetGeocodedDataWithRealAddressWithLocale()
    {
        $provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'fr_FR');
        $result   = $provider->getGeocodedData('10 allée Evariste Galois, Clermont ferrand');

        $this->assertEquals(45.7586841, $result['latitude'], '', 0.01);
        $this->assertEquals(3.1354075, $result['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(45.7576484680176, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(3.13258004188538, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(45.7595367431641, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(3.13707232475281, $result['bounds']['east'], '', 0.01);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Allée Évariste Galois', $result['streetName']);
        $this->assertEquals('63170', $result['zipcode']);
        //$this->assertEquals('Aubière', $result['city']);
        $this->assertEquals('La Pardieu', $result['cityDistrict']);
        //$this->assertEquals('Puy-de-Dôme', $result['county']);
        $this->assertEquals('Auvergne', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        $provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getReversedData(array('60.4539471728726', '22.2567841926781'));

        $this->assertEquals(60.4539471768582, $result['latitude'], '', 0.0001);
        $this->assertEquals(22.2567842183875, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertEquals(35, $result['streetNumber']);
        $this->assertEquals('Läntinen Pitkäkatu', $result['streetName']);
        $this->assertEquals(20100, $result['zipcode']);
        $this->assertEquals('Turku', $result['city']);
        $this->assertEquals('VII', $result['cityDistrict']);
        //$this->assertEquals('Finland Proper', $result['county']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        //$this->assertEquals('Finland', $result['country']);
        $this->assertEquals('FI', $result['countryCode']);
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://nominatim.openstreetmap.org/search?q=Hammm&format=xml&addressdetails=1&limit=1
     */
    public function testGetGeocodedDataWithUnknownCity()
    {
        $provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $provider->getGeocodedData('Hammm');
    }

    public function testGetReversedDataWithRealCoordinatesWithLocale()
    {
        $provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'de_DE');
        $result   = $provider->getGeocodedData('Kalbacher Hauptstraße, 60437 Frankfurt, Germany');

        $this->assertEquals(50.1856803, $result['latitude'], '', 0.01);
        $this->assertEquals(8.6506285, $result['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(50.1851196289062, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(8.64984607696533, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(50.1860122680664, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(8.65207576751709, $result['bounds']['east'], '', 0.01);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Kalbacher Hauptstraße', $result['streetName']);
        $this->assertEquals(60437, $result['zipcode']);
        $this->assertEquals('Frankfurt am Main', $result['city']);
        $this->assertEquals('Kalbach', $result['cityDistrict']);
        $this->assertEquals('Frankfurt am Main', $result['county']);
        $this->assertEquals('Hessen', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('Deutschland', $result['country']);
        $this->assertEquals('DE', $result['countryCode']);
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new OpenStreetMapsProvider($this->getMockAdapter($this->never()));
        $result   = $provider->getGeocodedData('127.0.0.1');

        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('zipcode', $result);
        $this->assertArrayNotHasKey('timezone', $result);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['county']);
        $this->assertEquals('localhost', $result['country']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The OpenStreetMapsProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new OpenStreetMapsProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('::1');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('88.188.221.14');

        $this->assertEquals(43.6189768, $result['latitude'], '', 0.01);
        $this->assertEquals(1.4564493, $result['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(43.6159553527832, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(1.45302963256836, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(43.623119354248, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(1.45882403850555, $result['bounds']['east'], '', 0.01);
        $this->assertNull($result['streetNumber']);
        //$this->assertEquals('Rue du Faubourg Bonnefoy', $result['streetName']);
        $this->assertEquals(31506, $result['zipcode']);
        $this->assertEquals(4, $result['cityDistrict']);
        $this->assertEquals('Toulouse', $result['city']);
        //$this->assertEquals('Haute-Garonne', $result['county']);
        $this->assertEquals('Midi-Pyrénées', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('France métropolitaine', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
    }

    public function testGetGeocodedDataWithRealIPv4WithLocale()
    {
        $provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'da_DK');
        $result   = $provider->getGeocodedData('88.188.221.14');

        $this->assertEquals(43.6142209, $result['latitude'], '', 0.0001);
        $this->assertEquals(1.4510706, $result['longitude'], '', 0.0001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(43.6141357421875, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(1.45088791847229, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(43.6150016784668, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(1.45196998119354, $result['bounds']['east'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Avenue de Lyon', $result['streetName']);
        $this->assertEquals(31506, $result['zipcode']);
        $this->assertEquals(4, $result['cityDistrict']);
        $this->assertEquals('Toulouse', $result['city']);
        //$this->assertEquals('Haute-Garonne', $result['county']);
        $this->assertEquals('Midi-Pyrénées', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('Frankrig', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The OpenStreetMapsProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('::ffff:88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not resolve address "Läntinen Pitkäkatu 35, Turku"
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new OpenStreetMapsProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('Läntinen Pitkäkatu 35, Turku');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://nominatim.openstreetmap.org/search?q=L%C3%A4ntinen+Pitk%C3%A4katu+35%2C+Turku&format=xml&addressdetails=1&limit=1
     */
    public function testGetGeocodedDataWithAddressGetsEmptyContent()
    {
        $provider = new OpenStreetMapsProvider($this->getMockAdapterReturns(''));
        $provider->getGeocodedData('Läntinen Pitkäkatu 35, Turku');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://nominatim.openstreetmap.org/search?q=L%C3%A4ntinen+Pitk%C3%A4katu+35%2C+Turku&format=xml&addressdetails=1&limit=1
     */
    public function testGetGeocodedDataWithAddressGetsEmptyXML()
    {
        $emptyXML = <<<XML
<?xml version="1.0" encoding="utf-8"?><searchresults_empty></searchresults_empty>
XML;
        $provider = new OpenStreetMapsProvider($this->getMockAdapterReturns($emptyXML));
        $provider->getGeocodedData('Läntinen Pitkäkatu 35, Turku');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Unable to resolve the coordinates 60.4539471728726, 22.2567841926781
     */
    public function testGetReversedDataWithCoordinatesGetsNullContent()
    {
        $provider = new OpenStreetMapsProvider($this->getMockAdapterReturns(null));
        $provider->getReversedData(array('60.4539471728726', '22.2567841926781'));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not resolve coordinates 60.4539471728726, 22.2567841926781
     */
    public function testGetReversedDataWithCoordinatesGetsEmptyContent()
    {
        $provider = new OpenStreetMapsProvider($this->getMockAdapterReturns(''));
        $provider->getReversedData(array('60.4539471728726', '22.2567841926781'));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not resolve coordinates -80.000000, -170.000000
     */
    public function testGetReversedDataWithCoordinatesGetsError()
    {
        $errorXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<reversegeocode querystring='format=xml&amp;lat=-80.000000&amp;lon=-170.000000&amp;addressdetails=1'>
    <error>Unable to geocode</error>
</reversegeocode>
XML;
        $provider = new OpenStreetMapsProvider($this->getMockAdapterReturns($errorXml));
        $provider->getReversedData(array('-80.000000', '-170.000000'));
    }
}
