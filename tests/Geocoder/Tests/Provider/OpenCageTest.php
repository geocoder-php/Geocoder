<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\OpenCage;

/**
 * @author mtm <mtm@opencagedata.com>
 */
class OpenCageTest extends TestCase
{
    public function testGetName()
    {
        $provider = new OpenCage($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('opencage', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not find results for query "http://api.opencagedata.com/geocode/v1/json?key=api_key&query=foobar&limit=5&pretty=1".
     */
    public function testGeocode()
    {
        $provider = new OpenCage($this->getMockAdapterReturns('{}'), 'api_key');
        $provider->geocode('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not find results for query "https://api.opencagedata.com/geocode/v1/json?key=api_key&query=foobar&limit=5&pretty=1".
     */
    public function testSslSchema()
    {
        $provider = new OpenCage($this->getMockAdapterReturns('{}'), 'api_key', true);
        $provider->geocode('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://api.opencagedata.com/geocode/v1/json?key=api_key&query=10+avenue+Gambetta%2C+Paris%2C+France&limit=5&pretty=1".
     */
    public function testGeocodeWithAddressGetsNullContent()
    {
        $provider = new OpenCage($this->getMockAdapterReturns(null), 'api_key');
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGeocodeWithRealAddress()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getAdapter($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY']);
        $results  = $provider->geocode('10 avenue Gambetta, Paris, France');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(3, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.866205, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.389089, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(48.863142699999997, $result->getBounds()->getSouth());
        $this->assertEquals(2.3890394000000001, $result->getBounds()->getWest());
        $this->assertEquals(48.863242700000001, $result->getBounds()->getNorth());
        $this->assertEquals(2.3891393999999999, $result->getBounds()->getEast());
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Ile-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
        $this->assertEquals('Europe/Paris', $result->getTimezone());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     */
    public function testReverse()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getMockAdapter(), $_SERVER['OPENCAGE_API_KEY']);
        $provider->reverse(1, 2);
    }

    public function testReverseWithRealCoordinates()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getAdapter($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY']);
        $results  = $provider->reverse(54.0484068, -2.7990345);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(54.0484068, $result->getLatitude(), '', 0.001);
        $this->assertEquals(-2.7990345, $result->getLongitude(), '', 0.001);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(54.048273100000003, $result->getBounds()->getSouth());
        $this->assertEquals(-2.7998815000000001, $result->getBounds()->getWest());
        $this->assertEquals(54.0494992, $result->getBounds()->getNorth());
        $this->assertEquals(-2.79813, $result->getBounds()->getEast());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Lancaster', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Lancashire', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('England', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United Kingdom', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountry()->getCode());
        $this->assertEquals('Europe/London' , $result->getTimezone());
    }

    public function testReverseWithVillage()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getAdapter($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY']);
        $results  = $provider->reverse(49.1390924, 1.6572462);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Bray-et-Lû', $result->getLocality());
    }

    public function testGeocodeWithCity()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getAdapter($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY']);
        $results  = $provider->geocode('Hanover');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(52.374478, $result->getLatitude(), '', 0.01);
        $this->assertEquals(9.738553, $result->getLongitude(), '', 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Region Hannover', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Lower Saxony', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Germany', $result->getCountry()->getName());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(37.744783, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-77.4464165, $result->getLongitude(), '', 0.01);
        $this->assertNull($result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Hanover', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('United States of America', $result->getCountry()->getName());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(18.3840489, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-78.131485, $result->getLongitude(), '', 0.01);
        $this->assertNull($result->getLocality());
        $this->assertTrue( $result->getAdminLevels()->has(2));
        $this->assertEquals('Hanover', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Jamaica', $result->getCountry()->getName());

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(3);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(43.7033073, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-72.2885663, $result->getLongitude(), '', 0.01);
        $this->assertEquals('Hanover', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Grafton County', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('New Hampshire', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States of America', $result->getCountry()->getName());
    }

    public function testGeocodeWithCityDistrict()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getAdapter($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY']);
        $results  = $provider->geocode('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(2, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(50.189062, $result->getLatitude(), '', 0.01);
        $this->assertEquals(8.636567, $result->getLongitude(), '', 0.01);
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('Kalbacher Hauptstraße', $result->getStreetName());
        $this->assertEquals(60437, $result->getPostalCode());
        $this->assertEquals('Frankfurt', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Frankfurt', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Hesse', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Germany', $result->getCountry()->getName());
        $this->assertEquals('DE', $result->getCountry()->getCode());
        $this->assertEquals('Europe/Berlin', $result->getTimezone());
    }

    public function testGeocodeWithLocale()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getAdapter($_SERVER['OPENCAGE_API_KEY']), $_SERVER['OPENCAGE_API_KEY'], true, 'es');
        $results  = $provider->geocode('London');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Londres', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Londres', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Inglaterra', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Reino Unido', $result->getCountry()->getName());
        $this->assertEquals('GB', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OpenCage provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new OpenCage($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OpenCage provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new OpenCage($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OpenCage provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv4()
    {
        $provider = new OpenCage($this->getAdapter(), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OpenCage provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = new OpenCage($this->getAdapter(), 'api_key');
        $provider->geocode('::ffff:74.200.247.59');
    }
}

class OpenCageMock extends OpenCage
{
    /**
     * Short circuits so assertions can inspect the
     * executed query URL
     */
    protected function executeQuery($query)
    {
        return $query;
    }
}
