<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\OpenStreetMapProvider;

class OpenStreetMapProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new OpenStreetMapProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('openstreetmap', $provider->getName());
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $provider = new OpenStreetMapProvider($this->getAdapter());
        $results  = $provider->getGeocodedData('Paris');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(48.8565056, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(2.3521334, $results[0]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[0]['bounds']);
        $this->assertArrayHasKey('west', $results[0]['bounds']);
        $this->assertArrayHasKey('north', $results[0]['bounds']);
        $this->assertArrayHasKey('east', $results[0]['bounds']);
        $this->assertEquals(48.8155250549316, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.22412180900574, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(48.902156829834, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.46976041793823, $results[0]['bounds']['east'], '', 0.01);
        $this->assertEquals(75000, $results[0]['zipcode']);
        $this->assertNull($results[0]['streetNumber']);
        $this->assertNull($results[0]['streetName']);
        $this->assertEquals('Paris', $results[0]['city']);
        $this->assertNull($results[0]['cityDistrict']);
        $this->assertEquals('Paris', $results[0]['county']);
        $this->assertEquals('Île-de-France', $results[0]['region']);
        $this->assertNull($results[0]['regionCode']);
        $this->assertEquals('France métropolitaine', $results[0]['country']);
        $this->assertEquals('FR', $results[0]['countryCode']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(48.8588408, $results[1]['latitude'], '', 0.01);
        $this->assertEquals(2.32003465529896, $results[1]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[1]['bounds']);
        $this->assertArrayHasKey('west', $results[1]['bounds']);
        $this->assertArrayHasKey('north', $results[1]['bounds']);
        $this->assertArrayHasKey('east', $results[1]['bounds']);
        $this->assertEquals(48.8155250549316, $results[1]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.22412180900574, $results[1]['bounds']['west'], '', 0.01);
        $this->assertEquals(48.902156829834, $results[1]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.46976041793823, $results[1]['bounds']['east'], '', 0.01);
        $this->assertNull($results[1]['zipcode']);
        $this->assertNull($results[1]['streetNumber']);
        $this->assertNull($results[1]['streetName']);
        $this->assertNull($results[1]['city']);
        $this->assertNull($results[1]['cityDistrict']);
        $this->assertEquals('Paris', $results[1]['county']);
        $this->assertEquals('Île-de-France', $results[1]['region']);
        $this->assertNull($results[1]['regionCode']);
        $this->assertEquals('France métropolitaine', $results[1]['country']);
        $this->assertEquals('FR', $results[1]['countryCode']);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(35.28687645, $results[2]['latitude'], '', 0.01);
        $this->assertEquals(-93.7354879210082, $results[2]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[2]['bounds']);
        $this->assertArrayHasKey('west', $results[2]['bounds']);
        $this->assertArrayHasKey('north', $results[2]['bounds']);
        $this->assertArrayHasKey('east', $results[2]['bounds']);
        $this->assertEquals(35.2672462463379, $results[2]['bounds']['south'], '', 0.01);
        $this->assertEquals(-93.7618103027344, $results[2]['bounds']['west'], '', 0.01);
        $this->assertEquals(35.3065032958984, $results[2]['bounds']['north'], '', 0.01);
        $this->assertEquals(-93.6750793457031, $results[2]['bounds']['east'], '', 0.01);
        $this->assertNull($results[2]['zipcode']);
        $this->assertNull($results[2]['streetNumber']);
        $this->assertNull($results[2]['streetName']);
        $this->assertEquals('Paris', $results[2]['city']);
        $this->assertNull($results[2]['cityDistrict']);
        $this->assertEquals('Logan County', $results[2]['county']);
        $this->assertEquals('Arkansas', $results[2]['region']);
        $this->assertNull($results[2]['regionCode']);
        $this->assertEquals('United States of America', $results[2]['country']);
        $this->assertEquals('US', $results[2]['countryCode']);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(33.6751155, $results[3]['latitude'], '', 0.01);
        $this->assertEquals(-95.5502662477703, $results[3]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[3]['bounds']);
        $this->assertArrayHasKey('west', $results[3]['bounds']);
        $this->assertArrayHasKey('north', $results[3]['bounds']);
        $this->assertArrayHasKey('east', $results[3]['bounds']);
        $this->assertEquals(33.6118507385254, $results[3]['bounds']['south'], '', 0.01);
        $this->assertEquals(-95.6279296875, $results[3]['bounds']['west'], '', 0.01);
        $this->assertEquals(33.7383804321289, $results[3]['bounds']['north'], '', 0.01);
        $this->assertEquals(-95.4354476928711, $results[3]['bounds']['east'], '', 0.01);
        $this->assertNull($results[3]['zipcode']);
        $this->assertNull($results[3]['streetNumber']);
        $this->assertNull($results[3]['streetName']);
        $this->assertEquals('Paris', $results[3]['city']);
        $this->assertNull($results[3]['cityDistrict']);
        $this->assertEquals('Lamar County', $results[3]['county']);
        $this->assertEquals('Texas', $results[3]['region']);
        $this->assertNull($results[3]['regionCode']);
        $this->assertEquals('United States of America', $results[3]['country']);
        $this->assertEquals('US', $results[3]['countryCode']);

        $this->assertInternalType('array', $results[4]);
        $this->assertEquals(38.2097987, $results[4]['latitude'], '', 0.01);
        $this->assertEquals(-84.2529869, $results[4]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[4]['bounds']);
        $this->assertArrayHasKey('west', $results[4]['bounds']);
        $this->assertArrayHasKey('north', $results[4]['bounds']);
        $this->assertArrayHasKey('east', $results[4]['bounds']);
        $this->assertEquals(38.1649208068848, $results[4]['bounds']['south'], '', 0.01);
        $this->assertEquals(-84.3073272705078, $results[4]['bounds']['west'], '', 0.01);
        $this->assertEquals(38.2382736206055, $results[4]['bounds']['north'], '', 0.01);
        $this->assertEquals(-84.2320861816406, $results[4]['bounds']['east'], '', 0.01);
        $this->assertNull($results[4]['zipcode']);
        $this->assertNull($results[4]['streetNumber']);
        $this->assertNull($results[4]['streetName']);
        $this->assertEquals('Paris', $results[4]['city']);
        $this->assertNull($results[4]['cityDistrict']);
        $this->assertEquals('Bourbon County', $results[4]['county']);
        $this->assertEquals('Kentucky', $results[4]['region']);
        $this->assertNull($results[4]['regionCode']);
        $this->assertEquals('United States of America', $results[4]['country']);
        $this->assertEquals('US', $results[4]['countryCode']);
    }

    public function testGetGeocodedDataWithRealAddressWithLocale()
    {
        $provider = new OpenStreetMapProvider($this->getAdapter(), 'fr_FR');
        $results  = $provider->getGeocodedData('10 allée Evariste Galois, Clermont ferrand');

        $this->assertInternalType('array', $results);
        $this->assertCount(2, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(45.7586841, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(3.1354075, $results[0]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[0]['bounds']);
        $this->assertArrayHasKey('west', $results[0]['bounds']);
        $this->assertArrayHasKey('north', $results[0]['bounds']);
        $this->assertArrayHasKey('east', $results[0]['bounds']);
        $this->assertEquals(45.7576484680176, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(3.13258004188538, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(45.7595367431641, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(3.13707232475281, $results[0]['bounds']['east'], '', 0.01);
        $this->assertNull($results[0]['streetNumber']);
        $this->assertEquals('Allée Évariste Galois', $results[0]['streetName']);
        $this->assertEquals('63170', $results[0]['zipcode']);
        $this->assertEquals('Clermont-Ferrand', $results[0]['city']);
        $this->assertEquals('La Pardieu', $results[0]['cityDistrict']);
        $this->assertEquals('Clermont-Ferrand', $results[0]['county']);
        $this->assertEquals('Auvergne', $results[0]['region']);
        $this->assertNull($results[0]['regionCode']);
        $this->assertEquals('France', $results[0]['country']);
        $this->assertEquals('FR', $results[0]['countryCode']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(45.7586841, $results[1]['latitude'], '', 0.01);
        $this->assertEquals(3.1354075, $results[1]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[1]['bounds']);
        $this->assertArrayHasKey('west', $results[1]['bounds']);
        $this->assertArrayHasKey('north', $results[1]['bounds']);
        $this->assertArrayHasKey('east', $results[1]['bounds']);
        $this->assertEquals(45.7576484680176, $results[1]['bounds']['south'], '', 0.01);
        $this->assertEquals(3.13258004188538, $results[1]['bounds']['west'], '', 0.01);
        $this->assertEquals(45.7595367431641, $results[1]['bounds']['north'], '', 0.01);
        $this->assertEquals(3.13707232475281, $results[1]['bounds']['east'], '', 0.01);
        $this->assertNull($results[1]['streetNumber']);
        $this->assertEquals('Allée Évariste Galois', $results[1]['streetName']);
        $this->assertEquals('63170', $results[1]['zipcode']);
        $this->assertEquals('Aubière', $results[1]['city']);
        $this->assertEquals('Cap Sud', $results[1]['cityDistrict']);
        $this->assertEquals('Clermont-Ferrand', $results[1]['county']);
        $this->assertEquals('Auvergne', $results[1]['region']);
        $this->assertNull($results[1]['regionCode']);
        $this->assertEquals('France', $results[1]['country']);
        $this->assertEquals('FR', $results[1]['countryCode']);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        $provider = new OpenStreetMapProvider($this->getAdapter());
        $results  = $provider->getReversedData(array('60.4539471728726', '22.2567841926781'));

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(60.4539, $result['latitude'], '', 0.001);
        $this->assertEquals(22.2568, $result['longitude'], '', 0.001);
        $this->assertNull($result['bounds']);
        $this->assertEquals(35, $result['streetNumber']);
        $this->assertEquals('Läntinen Pitkäkatu', $result['streetName']);
        $this->assertEquals(20100, $result['zipcode']);
        $this->assertEquals('Turku', $result['city']);
        $this->assertEquals('VII', $result['cityDistrict']);
        $this->assertEquals('Lounais-Suomen aluehallintovirasto', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('FI', $result['countryCode']);
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://nominatim.openstreetmap.org/search?q=Hammm&format=xml&addressdetails=1&limit=5
     */
    public function testGetGeocodedDataWithUnknownCity()
    {
        $provider = new OpenStreetMapProvider($this->getAdapter());
        $provider->getGeocodedData('Hammm');
    }

    public function testGetReversedDataWithRealCoordinatesWithLocale()
    {
        $provider = new OpenStreetMapProvider($this->getAdapter(), 'de_DE');
        $results  = $provider->getGeocodedData('Kalbacher Hauptstraße, 60437 Frankfurt, Germany');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(50.1856803, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(8.6506285, $results[0]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[0]['bounds']);
        $this->assertArrayHasKey('west', $results[0]['bounds']);
        $this->assertArrayHasKey('north', $results[0]['bounds']);
        $this->assertArrayHasKey('east', $results[0]['bounds']);
        $this->assertEquals(50.1851196289062, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(8.64984607696533, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(50.1860122680664, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(8.65207576751709, $results[0]['bounds']['east'], '', 0.01);
        $this->assertNull($results[0]['streetNumber']);
        $this->assertEquals('Kalbacher Hauptstraße', $results[0]['streetName']);
        $this->assertEquals(60437, $results[0]['zipcode']);
        $this->assertEquals('Frankfurt am Main', $results[0]['city']);
        $this->assertEquals('Kalbach', $results[0]['cityDistrict']);
        $this->assertEquals('Frankfurt am Main', $results[0]['county']);
        $this->assertEquals('Hessen', $results[0]['region']);
        $this->assertNull($results[0]['regionCode']);
        $this->assertEquals('Deutschland', $results[0]['country']);
        $this->assertEquals('DE', $results[0]['countryCode']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(50.1845911, $results[1]['latitude'], '', 0.01);
        $this->assertEquals(8.6540194, $results[1]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[1]['bounds']);
        $this->assertArrayHasKey('west', $results[1]['bounds']);
        $this->assertArrayHasKey('north', $results[1]['bounds']);
        $this->assertArrayHasKey('east', $results[1]['bounds']);
        $this->assertEquals(50.1840019226074, $results[1]['bounds']['south'], '', 0.01);
        $this->assertEquals(8.65207481384277, $results[1]['bounds']['west'], '', 0.01);
        $this->assertEquals(50.1851234436035, $results[1]['bounds']['north'], '', 0.01);
        $this->assertEquals(8.65643787384033, $results[1]['bounds']['east'], '', 0.01);
        $this->assertNull($results[1]['streetNumber']);
        $this->assertEquals('Kalbacher Hauptstraße', $results[1]['streetName']);
        $this->assertEquals(60437, $results[1]['zipcode']);
        $this->assertEquals('Frankfurt am Main', $results[1]['city']);
        $this->assertEquals('Bonames', $results[1]['cityDistrict']);
        $this->assertEquals('Frankfurt am Main', $results[1]['county']);
        $this->assertEquals('Hessen', $results[1]['region']);
        $this->assertNull($results[1]['regionCode']);
        $this->assertEquals('Deutschland', $results[1]['country']);
        $this->assertEquals('DE', $results[1]['countryCode']);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(50.1862884, $results[2]['latitude'], '', 0.01);
        $this->assertEquals(8.6493167, $results[2]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[2]['bounds']);
        $this->assertArrayHasKey('west', $results[2]['bounds']);
        $this->assertArrayHasKey('north', $results[2]['bounds']);
        $this->assertArrayHasKey('east', $results[2]['bounds']);
        $this->assertEquals(50.1862106323242, $results[2]['bounds']['south'], '', 0.01);
        $this->assertEquals(8.64931583404541, $results[2]['bounds']['west'], '', 0.01);
        $this->assertEquals(50.1862907409668, $results[2]['bounds']['north'], '', 0.01);
        $this->assertEquals(8.64943981170654, $results[2]['bounds']['east'], '', 0.01);
        $this->assertNull($results[2]['streetNumber']);
        $this->assertEquals('Kalbacher Hauptstraße', $results[2]['streetName']);
        $this->assertEquals(60437, $results[2]['zipcode']);
        $this->assertEquals('Frankfurt am Main', $results[2]['city']);
        $this->assertEquals('Kalbach', $results[2]['cityDistrict']);
        $this->assertEquals('Frankfurt am Main', $results[2]['county']);
        $this->assertEquals('Hessen', $results[2]['region']);
        $this->assertNull($results[2]['regionCode']);
        $this->assertEquals('Deutschland', $results[2]['country']);
        $this->assertEquals('DE', $results[2]['countryCode']);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(50.1861344, $results[3]['latitude'], '', 0.01);
        $this->assertEquals(8.649578, $results[3]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[3]['bounds']);
        $this->assertArrayHasKey('west', $results[3]['bounds']);
        $this->assertArrayHasKey('north', $results[3]['bounds']);
        $this->assertArrayHasKey('east', $results[3]['bounds']);
        $this->assertEquals(50.1860084533691, $results[3]['bounds']['south'], '', 0.01);
        $this->assertEquals(8.64943885803223, $results[3]['bounds']['west'], '', 0.01);
        $this->assertEquals(50.1862144470215, $results[3]['bounds']['north'], '', 0.01);
        $this->assertEquals(8.64984703063965, $results[3]['bounds']['east'], '', 0.01);
        $this->assertNull($results[3]['streetNumber']);
        $this->assertEquals('Kalbacher Hauptstraße', $results[3]['streetName']);
        $this->assertEquals(60437, $results[3]['zipcode']);
        $this->assertEquals('Frankfurt am Main', $results[3]['city']);
        $this->assertEquals('Kalbach', $results[3]['cityDistrict']);
        $this->assertEquals('Frankfurt am Main', $results[3]['county']);
        $this->assertEquals('Hessen', $results[3]['region']);
        $this->assertNull($results[3]['regionCode']);
        $this->assertEquals('Deutschland', $results[3]['country']);
        $this->assertEquals('DE', $results[3]['countryCode']);
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new OpenStreetMapProvider($this->getMockAdapter($this->never()));
        $result   = $provider->getGeocodedData('127.0.0.1');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
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
     * @expectedExceptionMessage The NominatimProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new OpenStreetMapProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('::1');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new OpenStreetMapProvider($this->getAdapter());
        $results  = $provider->getGeocodedData('88.188.221.14');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(43.6189768, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(1.4564493, $results[0]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[0]['bounds']);
        $this->assertArrayHasKey('west', $results[0]['bounds']);
        $this->assertArrayHasKey('north', $results[0]['bounds']);
        $this->assertArrayHasKey('east', $results[0]['bounds']);
        $this->assertEquals(43.6159553527832, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(1.45302963256836, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(43.623119354248, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(1.45882403850555, $results[0]['bounds']['east'], '', 0.01);
        $this->assertNull($results[0]['streetNumber']);
        //$this->assertEquals('Rue du Faubourg Bonnefoy', $results[0]['streetName']);
        $this->assertEquals(31506, $results[0]['zipcode']);
        $this->assertEquals(4, $results[0]['cityDistrict']);
        $this->assertEquals('Toulouse', $results[0]['city']);
        //$this->assertEquals('Haute-Garonne', $results[0]['county']);
        $this->assertEquals('Midi-Pyrénées', $results[0]['region']);
        $this->assertNull($results[0]['regionCode']);
        $this->assertEquals('France métropolitaine', $results[0]['country']);
        $this->assertEquals('FR', $results[0]['countryCode']);

        $this->assertInternalType('array', $results[1]);
        $this->assertInternalType('array', $results[2]);
        $this->assertInternalType('array', $results[3]);
        $this->assertInternalType('array', $results[4]);
    }

    public function testGetGeocodedDataWithRealIPv4WithLocale()
    {
        $provider = new OpenStreetMapProvider($this->getAdapter(), 'da_DK');
        $results  = $provider->getGeocodedData('88.188.221.14');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(43.6155351, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(1.4525647, $results[0]['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $results[0]['bounds']);
        $this->assertArrayHasKey('west', $results[0]['bounds']);
        $this->assertArrayHasKey('north', $results[0]['bounds']);
        $this->assertArrayHasKey('east', $results[0]['bounds']);
        $this->assertEquals(43.6154556274414, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(1.4524964094162, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(43.6156005859375, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(1.45262920856476, $results[0]['bounds']['east'], '', 0.01);
        $this->assertNull($results[0]['streetNumber']);
        $this->assertEquals('Rue du Faubourg Bonnefoy', $results[0]['streetName']);
        $this->assertEquals(31506, $results[0]['zipcode']);
        $this->assertEquals(4, $results[0]['cityDistrict']);
        $this->assertEquals('Toulouse', $results[0]['city']);
        $this->assertEquals('Toulouse', $results[0]['county']);
        $this->assertEquals('Midi-Pyrénées', $results[0]['region']);
        $this->assertNull($results[0]['regionCode']);
        $this->assertEquals('Frankrig', $results[0]['country']);
        $this->assertEquals('FR', $results[0]['countryCode']);

        $this->assertInternalType('array', $results[1]);
        $this->assertInternalType('array', $results[2]);
        $this->assertInternalType('array', $results[3]);
        $this->assertInternalType('array', $results[4]);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The NominatimProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new OpenStreetMapProvider($this->getAdapter());
        $provider->getGeocodedData('::ffff:88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not resolve address "Läntinen Pitkäkatu 35, Turku"
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new OpenStreetMapProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('Läntinen Pitkäkatu 35, Turku');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://nominatim.openstreetmap.org/search?q=L%C3%A4ntinen+Pitk%C3%A4katu+35%2C+Turku&format=xml&addressdetails=1&limit=5
     */
    public function testGetGeocodedDataWithAddressGetsEmptyContent()
    {
        $provider = new OpenStreetMapProvider($this->getMockAdapterReturns(''));
        $provider->getGeocodedData('Läntinen Pitkäkatu 35, Turku');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://nominatim.openstreetmap.org/search?q=L%C3%A4ntinen+Pitk%C3%A4katu+35%2C+Turku&format=xml&addressdetails=1&limit=5
     */
    public function testGetGeocodedDataWithAddressGetsEmptyXML()
    {
        $emptyXML = <<<XML
<?xml version="1.0" encoding="utf-8"?><searchresults_empty></searchresults_empty>
XML;
        $provider = new OpenStreetMapProvider($this->getMockAdapterReturns($emptyXML));
        $provider->getGeocodedData('Läntinen Pitkäkatu 35, Turku');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Unable to resolve the coordinates 60.4539471728726, 22.2567841926781
     */
    public function testGetReversedDataWithCoordinatesGetsNullContent()
    {
        $provider = new OpenStreetMapProvider($this->getMockAdapterReturns(null));
        $provider->getReversedData(array('60.4539471728726', '22.2567841926781'));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not resolve coordinates 60.4539471728726, 22.2567841926781
     */
    public function testGetReversedDataWithCoordinatesGetsEmptyContent()
    {
        $provider = new OpenStreetMapProvider($this->getMockAdapterReturns(''));
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
        $provider = new OpenStreetMapProvider($this->getMockAdapterReturns($errorXml));
        $provider->getReversedData(array('-80.000000', '-170.000000'));
    }

    public function testGetNodeStreetName()
    {
        $provider = new OpenStreetMapProvider($this->getAdapter(), 'fr_FR');
        $results  = $provider->getReversedData(array(48.86, 2.35));

        $this->assertEquals('Rue Quincampoix', $results[0]['streetName']);
    }
}
