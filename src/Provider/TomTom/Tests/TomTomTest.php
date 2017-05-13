<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\TomTom\Tests;

use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Tests\TestCase;
use Geocoder\Provider\TomTom\TomTom;

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
        $provider->geocodeQuery(GeocodeQuery::create('foo'));
    }

    /**
     * @expectedException \Geocoder\Exception\ZeroResults
     */
    public function testGeocodeWithAddressContentReturnNull()
    {
        $provider = new TomTom($this->getMockAdapterReturns(null), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('Tagensvej 47, 2200 København N'));
    }

    /**
     * @expectedException \Geocoder\Exception\ZeroResults
     */
    public function testGeocodeWithAddress()
    {
        $provider = new TomTom($this->getMockAdapter(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('Tagensvej 47, 2200 København N'));
    }

    /**
     * @expectedException \Geocoder\Exception\ZeroResults
     */
    public function testGeocodeZeroResults()
    {
        $ZeroResults = <<<'XML'
<geoResponse duration="" debugInformation="" count="0" svnRevision="" version="" consolidatedMaps=""/>
XML;

        $provider = new TomTom($this->getMockAdapterReturns($ZeroResults), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('foo'));
    }

    public function testGeocodeWithRealAddress()
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Tagensvej 47, 2200 København N'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(55.704389, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(12.546129, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNull($result->getBounds());
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

        $provider = new TomTom($this->getAdapter($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Tagensvej 47, 2200 København N')->withLocale('fr_FR'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(55.704389, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(12.546129, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNull($result->getBounds());
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

        $provider = new TomTom($this->getAdapter($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Tagensvej 47, 2200 København N')->withLocale('sv-SE'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(55.704389, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(12.546129, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNull($result->getBounds());
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
        $results = $provider->geocodeQuery(GeocodeQuery::create('Paris'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.856898, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(2.350844, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNull($result->getBounds());
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

        /** @var Location $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(33.661426, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(-95.556321, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Texas', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(36.302754, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(-88.326359, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Tennessee', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(3);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(-19.039448, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(29.560445, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Midlands', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Zimbabwe', $result->getCountry()->getName());
        $this->assertEquals('ZWE', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(4);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(35.292105, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(-93.729922, $result->getCoordinates()->getLongitude(), '', 0.0001);
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
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TomTom provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new TomTom($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TomTom provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithIPv4()
    {
        $provider = new TomTom($this->getAdapter(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TomTom provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithIPv6()
    {
        $provider = new TomTom($this->getAdapter(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage No Map API Key provided
     */
    public function testReverseWithoutApiKey()
    {
        $provider = new TomTom($this->getMockAdapter($this->never()), null);
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\ZeroResults
     */
    public function testReverse()
    {
        $provider = new TomTom($this->getMockAdapter(), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\ZeroResults
     */
    public function testReverseWithCoordinatesContentReturnNull()
    {
        $provider = new TomTom($this->getMockAdapterReturns(null), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(48.86321648955345, 2.3887719959020615));
    }

    /**
     * @expectedException \Geocoder\Exception\ZeroResults
     */
    public function testReverseWithCoordinatesGetsEmptyContent()
    {
        $provider = new TomTom($this->getMockAdapterReturns(''), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates('60.4539471728726', '22.2567841926781'));
    }

    /**
     * @expectedException \Geocoder\Exception\ZeroResults
     */
    public function testReverseError400()
    {
        $error400 = <<<'XML'
<errorResponse version="" description="" errorCode="400"/>
XML;

        $provider = new TomTom($this->getMockAdapterReturns($error400), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage Map API Key provided is not valid.
     */
    public function testReverseError403()
    {
        $error403 = <<<'XML'
<errorResponse version="" description="" errorCode="403"/>
XML;

        $provider = new TomTom($this->getMockAdapterReturns($error403), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    public function testReverseWithRealCoordinates()
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getAdapter($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.86321648955345, 2.3887719959020615));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.86323, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(2.38877, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNull($result->getBounds());
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

        $provider = new TomTom($this->getAdapter($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(56.5231, 10.0659));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(56.52435, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(10.06744, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNull($result->getBounds());
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
