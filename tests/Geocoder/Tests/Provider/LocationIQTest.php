<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\LocationIQ;

/**
 * @author Saikiran Ch <contact@unwiredlabs.com>
 */
Class LocationIQTest extends TestCase
{
    public function testGetName()
    {
        echo __FUNCTION__;
        $provider = new LocationIQ($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('locationiq', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage LocationIQ does not support IP addresses
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        echo __FUNCTION__;
        $provider = new LocationIQ($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage No API Key provided.   
     */
    public function testGeocodeWithInvalidCredentials()
    {
        echo __FUNCTION__;
        $provider = new LocationIQ($this->getMockAdapter($this->never()), null);
        $provider->geocode('Hyderabad');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage  Could not execute query http://locationiq.org/v1/search.php?format=xml&addressdetails=1&key=api_key&q=&limit=5
     */
    public function testGeocodeWithAddressGetsNullContent()
    {
        echo __FUNCTION__;
        $provider = new LocationIQ($this->getMockAdapter(), 'api_key');
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage  API Key is not valid http://locationiq.org/v1/search.php?format=xml&addressdetails=1&key=api_key&q=Hyderabad&limit=5
     */
    public function testGeocodeWithInvalidKey()
    {
        echo __FUNCTION__;
        $provider = new LocationIQ($this->getAdapter(), 'api_key');
        $provider->geocode('Hyderabad');
    }

    public function testGeocodeWithRealAddress()
    {
        echo __FUNCTION__;
        if (!isset($_SERVER['LOCATIONIQ_API_KEY'])) {
            $this->markTestSkipped('You need to configure the LOCATIONIQ_API_KEY value in phpunit.xml');
        }

        $provider = new LocationIQ($this->getAdapter(), $_SERVER['LOCATIONIQ_API_KEY']);
        $results  = $provider->geocode('Paris');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.8565056, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.3521334, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(48.8155250549316, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(2.22412180900574, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(48.902156829834, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(2.46976041793823, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals(75000, $result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Ile-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.8588408, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.32003465529896, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
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
        $this->assertEquals('Ile-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(35.28687645, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-93.7354879210082, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
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

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(3);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(33.6617961, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-95.5502662477703, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
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

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(4);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(38.2097987, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-84.2529869, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
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

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage No API Key provided.   
     */
    public function testReverseWithInvalidCredentials()
    {
        echo __FUNCTION__;
        $provider = new LocationIQ($this->getMockAdapter($this->never()), null);
        $provider->reverse(60.453947, 22.256784);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Unable to find results for coordinates [ 60.453947, 22.256784 ].
     */
    public function testReverseWithCoordinatesGetsNullContent()
    {
        echo __FUNCTION__;
        $provider = new LocationIQ($this->getMockAdapterReturns(null), 'api_key');
        $provider->reverse(60.453947, 22.256784);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Unable to find results for coordinates [ 60.453947, 22.256784 ].
     */
    public function testReverseWithCoordinatesGetsEmptyContent()
    {
        echo __FUNCTION__;
        $provider = new LocationIQ($this->getMockAdapterReturns('<error></error>'), 'api_key');
        $provider->reverse(60.453947, 22.256784);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Unable to find results for coordinates [ -80.000000, -170.000000 ].
     */
    public function testReverseWithCoordinatesGetsError()
    {
        echo __FUNCTION__;
        $errorXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<reversegeocode querystring='format=xml&amp;lat=-80.000000&amp;lon=-170.000000&amp;addressdetails=1'>
    <error>Unable to geocode</error>
</reversegeocode>
XML;
        $provider = new LocationIQ($this->getMockAdapterReturns($errorXml), 'api_key');
        $provider->reverse(-80.000000, -170.000000);
    }

    public function testGetNodeStreetName()
    {
        echo __FUNCTION__;
        if (!isset($_SERVER['LOCATIONIQ_API_KEY'])) {
            $this->markTestSkipped('You need to configure the LOCATIONIQ_API_KEY value in phpunit.xml');
        }

        $provider = new LocationIQ($this->getAdapter(), $_SERVER['LOCATIONIQ_API_KEY']);
        $results  = $provider->reverse(48.86, 2.35);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Rue Quincampoix', $result->getStreetName());
    }

    public function testReverseWithRealCoordinates()
    {
        echo __FUNCTION__;
        if (!isset($_SERVER['LOCATIONIQ_API_KEY'])) {
            $this->markTestSkipped('You need to configure the LOCATIONIQ_API_KEY value in phpunit.xml');
        }

        $provider = new LocationIQ($this->getAdapter(), $_SERVER['LOCATIONIQ_API_KEY']);
        $results  = $provider->geocode('Kalbacher Hauptstraße, 60437 Frankfurt, Germany');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(4, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(50.1856803, $result->getLatitude(), '', 0.01);
        $this->assertEquals(8.6506285, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(50.1851196289062, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(8.64984607696533, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(50.1860122680664, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(8.65207576751709, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Kalbacher Hauptstraße', $result->getStreetName());
        $this->assertEquals(60437, $result->getPostalCode());
        $this->assertEquals('Kalbach', $result->getSubLocality());
        $this->assertEquals('Frankfurt', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Hesse', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Frankfurt', $result->getAdminLevels()->get(2)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Germany', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(50.1845911, $result->getLatitude(), '', 0.01);
        $this->assertEquals(8.6540194, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(50.1840019226074, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(8.65207481384277, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(50.1851234436035, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(8.65643787384033, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Kalbacher Hauptstraße', $result->getStreetName());
        $this->assertEquals(60437, $result->getPostalCode());
        $this->assertEquals('Kalbach', $result->getSubLocality());
        $this->assertEquals('Frankfurt', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Hesse', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Frankfurt', $result->getAdminLevels()->get(2)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Germany', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(50.1862884, $result->getLatitude(), '', 0.01);
        $this->assertEquals(8.6493167, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(50.1862106323242, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(8.64931583404541, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(50.1862907409668, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(8.64943981170654, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Kalbacher Hauptstraße', $result->getStreetName());
        $this->assertEquals(60437, $result->getPostalCode());
        $this->assertEquals('Kalbach', $result->getSubLocality());
        $this->assertEquals('Frankfurt', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Hesse', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Frankfurt', $result->getAdminLevels()->get(2)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Germany', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(3);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(50.1861344, $result->getLatitude(), '', 0.01);
        $this->assertEquals(8.649578, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(50.1860084533691, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(8.64943885803223, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(50.1862144470215, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(8.64984703063965, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Kalbacher Hauptstraße', $result->getStreetName());
        $this->assertEquals(60437, $result->getPostalCode());
        $this->assertEquals('Bonames', $result->getSubLocality());
        $this->assertEquals('Frankfurt', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Hesse', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Frankfurt', $result->getAdminLevels()->get(2)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Germany', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());
    }
}