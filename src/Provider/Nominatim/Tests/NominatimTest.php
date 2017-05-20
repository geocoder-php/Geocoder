<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Nominatim\Tests;

use Geocoder\Collection;
use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\Nominatim\Nominatim;
use Geocoder\Tests\TestCase;

class NominatimTest extends TestCase
{
    public function testGeocodeWithRealAddress()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('Paris'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.8565056, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.3521334, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.8155250549316, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(2.22412180900574, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(48.902156829834, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(2.46976041793823, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Île-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.8588408, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.32003465529896, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.8155250549316, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(2.22412180900574, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(48.902156829834, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(2.46976041793823, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Île-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(35.28687645, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-93.7354879210082, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(35.2672462463379, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(-93.7618103027344, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(35.3065032958984, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(-93.6750793457031, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Logan County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Arkansas', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States of America', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(3);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(33.6751155, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-95.5502662477703, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(33.6118507385254, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(-95.6279296875, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(33.7383804321289, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(-95.4354476928711, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Lamar County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Texas', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States of America', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(4);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(38.2097987, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-84.2529869, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(38.1649208068848, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(-84.3073272705078, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(38.2382736206055, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(-84.2320861816406, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Bourbon County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Kentucky', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States of America', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    public function testGeocodeWithRealAddressWithLocale()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 allée Evariste Galois, Clermont ferrand')->withLocale('fr_FR'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(2, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(45.7586841, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(3.1354075, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(45.7576484680176, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(3.13258004188538, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(45.7595367431641, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(3.13707232475281, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Allée Évariste Galois', $result->getStreetName());
        $this->assertEquals('63000', $result->getPostalCode());
        $this->assertEquals('La Pardieu', $result->getSubLocality());
        $this->assertEquals('Clermont-Ferrand', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Clermont-Ferrand', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Auvergne', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(45.7586841, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(3.1354075, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(45.7576484680176, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(3.13258004188538, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(45.7595367431641, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(3.13707232475281, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Allée Évariste Galois', $result->getStreetName());
        $this->assertEquals('63170', $result->getPostalCode());
        $this->assertEquals('Cap Sud', $result->getSubLocality());
        $this->assertNull($result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Clermont-Ferrand', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Auvergne', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
    }

    public function testReverseWithRealCoordinates()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getAdapter());
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(60.4539471728726, 22.2567841926781));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(60.4539, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(22.2568, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNull($result->getBounds());
        $this->assertEquals(35, $result->getStreetNumber());
        $this->assertEquals('Läntinen Pitkäkatu', $result->getStreetName());
        $this->assertEquals(20100, $result->getPostalCode());
        $this->assertEquals('VII', $result->getSubLocality());
        $this->assertEquals('Turku', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Varsinais-Suomi', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Etelä-Suomi', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Suomi', $result->getCountry()->getName());
        $this->assertEquals('FI', $result->getCountry()->getCode());
    }

    public function testGeocodeWithUnknownCity()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getAdapter());
        $result = $provider->geocodeQuery(GeocodeQuery::create('Hammm'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testReverseWithRealCoordinatesWithLocale()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('Kalbacher Hauptstraße, 60437 Frankfurt, Germany')->withLocale('de_DE'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(50.1856803, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(8.6506285, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(50.1851196289062, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(8.64984607696533, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(50.1860122680664, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(8.65207576751709, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Kalbacher Hauptstraße', $result->getStreetName());
        $this->assertEquals(60437, $result->getPostalCode());
        $this->assertEquals('Kalbach', $result->getSubLocality());
        $this->assertEquals('Frankfurt am Main', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Hessen', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Deutschland', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(50.1845911, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(8.6540194, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(50.1840019226074, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(8.65207481384277, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(50.1851234436035, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(8.65643787384033, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Kalbacher Hauptstraße', $result->getStreetName());
        $this->assertEquals(60437, $result->getPostalCode());
        $this->assertEquals('Kalbach', $result->getSubLocality());
        $this->assertEquals('Frankfurt am Main', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Hessen', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Deutschland', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(50.1862884, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(8.6493167, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(50.1862106323242, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(8.64931583404541, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(50.1862907409668, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(8.64943981170654, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Kalbacher Hauptstraße', $result->getStreetName());
        $this->assertEquals(60437, $result->getPostalCode());
        $this->assertEquals('Kalbach', $result->getSubLocality());
        $this->assertEquals('Frankfurt am Main', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Hessen', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Deutschland', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(3);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(50.1861344, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(8.649578, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(50.1860084533691, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(8.64943885803223, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(50.1862144470215, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(8.64984703063965, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Kalbacher Hauptstraße', $result->getStreetName());
        $this->assertEquals(60437, $result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertEquals('Frankfurt am Main', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Hessen', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Deutschland', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getMockAdapter($this->never()));
        $results = $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEmpty($result->getAdminLevels());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Nominatim provider does not support IPv6 addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getMockAdapter($this->never()));
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIPv4()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('88.188.221.14'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(43.6189768, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(1.4564493, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(43.6159553527832, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(1.45302963256836, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(43.623119354248, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(1.45882403850555, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Avenue de Lyon', $result->getStreetName());
        $this->assertEquals(31506, $result->getPostalCode());
        $this->assertEquals(4, $result->getSubLocality());
        $this->assertEquals('Toulouse', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Toulouse', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Midi-Pyrénées', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('France métropolitaine', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
    }

    public function testGeocodeWithRealIPv4WithLocale()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('88.188.221.14')->withLocale('da_DK'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(43.6155351, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(1.4525647, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(43.6154556274414, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(1.4524964094162, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(43.6156005859375, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(1.45262920856476, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Rue du Faubourg Bonnefoy', $result->getStreetName());
        $this->assertEquals(31506, $result->getPostalCode());
        $this->assertEquals(4, $result->getSubLocality());
        $this->assertEquals('Toulouse', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Toulouse', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Midi-Pyrénées', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Frankrig', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Nominatim provider does not support IPv6 addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getAdapter());
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocodeWithAddressGetsNullContent()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getMockAdapterReturns(null));
        $provider->geocodeQuery(GeocodeQuery::create('Läntinen Pitkäkatu 35, Turku'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocodeWithAddressGetsEmptyContent()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getMockAdapterReturns('<foo></foo>'));
        $provider->geocodeQuery(GeocodeQuery::create('Läntinen Pitkäkatu 35, Turku'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocodeWithAddressGetsEmptyXML()
    {
        $emptyXML = <<<'XML'
<?xml version="1.0" encoding="utf-8"?><searchresults_empty></searchresults_empty>
XML;
        $provider = Nominatim::withOpenStreetMapServer($this->getMockAdapterReturns($emptyXML));
        $provider->geocodeQuery(GeocodeQuery::create('Läntinen Pitkäkatu 35, Turku'));
    }

    public function testReverseWithCoordinatesGetsNullContent()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getMockAdapterReturns(null));
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(60.4539471728726, 22.2567841926781));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testReverseWithCoordinatesGetsEmptyContent()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getMockAdapterReturns('<error></error>'));
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(60.4539471728726, 22.2567841926781));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testReverseWithCoordinatesGetsError()
    {
        $errorXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8" ?>
<reversegeocode querystring='format=xml&amp;lat=-80.000000&amp;lon=-170.000000&amp;addressdetails=1'>
    <error>Unable to geocode</error>
</reversegeocode>
XML;
        $provider = Nominatim::withOpenStreetMapServer($this->getMockAdapterReturns($errorXml));
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(-80.000000, -170.000000));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGetNodeStreetName()
    {
        $provider = Nominatim::withOpenStreetMapServer($this->getAdapter());
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.86, 2.35));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Rue Quincampoix', $result->getStreetName());
    }
}
