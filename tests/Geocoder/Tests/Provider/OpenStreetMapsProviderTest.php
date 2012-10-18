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

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not resolve address "Läntinen Pitkäkatu 35, Turku"
     */
    public function testGetGeocodedDataWithAddressContentReturnNull()
    {
        $provider = new OpenStreetMapsProvider($this->getMockAdapterGetContentReturnNull());
        $provider->getGeocodedData('Läntinen Pitkäkatu 35, Turku');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://nominatim.openstreetmap.org/search?q=L%C3%A4ntinen+Pitk%C3%A4katu+35%2C+Turku&format=xml&addressdetails=1
     */
    public function testGetGeocodedDataWithAddressContentReturnNothing()
    {
        $mockReturnNothing = $this->getMock('Geocoder\\HttpAdapter\\HttpAdapterInterface');
        $mockReturnNothing
            ->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue(''));
        $provider = new OpenStreetMapsProvider($mockReturnNothing);
        $provider->getGeocodedData('Läntinen Pitkäkatu 35, Turku');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://nominatim.openstreetmap.org/search?q=L%C3%A4ntinen+Pitk%C3%A4katu+35%2C+Turku&format=xml&addressdetails=1
     */
    public function testGetGeocodedDataWithAddressContentReturnEmptyXML()
    {
        $emptyXML = <<<XML
<?xml version="1.0" encoding="utf-8"?><searchresults_empty></searchresults_empty>
XML;
        $mockReturnEmptyXML = $this->getMock('Geocoder\\HttpAdapter\\HttpAdapterInterface');
        $mockReturnEmptyXML
            ->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($emptyXML));
        $provider = new OpenStreetMapsProvider($mockReturnEmptyXML);
        $provider->getGeocodedData('Läntinen Pitkäkatu 35, Turku');
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
        $this->assertEquals('Finland Proper', $result['county']);
        $this->assertEquals(null, $result['region']);
        $this->assertEquals('Finland', $result['country']);
        $this->assertEquals('FI', $result['countryCode']);

        $result   = $provider->getGeocodedData('10 allée Evariste Galois, Clermont ferrand');

        $this->assertEquals(45.7587754693841, $result['latitude'], '', 0.01);
        $this->assertEquals(3.13073228527092, $result['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(45.7595672607422, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(3.12900018692017, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(45.7605743408203, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(3.1324610710144, $result['bounds']['east'], '', 0.01);
        $this->assertEquals(null, $result['streetNumber']);
        $this->assertEquals('Allée Évariste Galois', $result['streetName']);
        $this->assertEquals('63170', $result['zipcode']);
        //$this->assertEquals('Clermont-Ferrand', $result['city']);
        $this->assertEquals('Puy-de-Dôme', $result['county']);
        $this->assertEquals('Auvergne', $result['region']);
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Unable to resolve the coordinates 60.4539471728726, 22.2567841926781
     */
    public function testGetReversedDataWithCoordinatesContentReturnNull()
    {
        $provider = new OpenStreetMapsProvider($this->getMockAdapterGetContentReturnNull());
        $provider->getReversedData(array('60.4539471728726', '22.2567841926781'));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not resolve coordinates 60.4539471728726, 22.2567841926781
     */
    public function testGetReversedDataWithCoordinatesContentReturnNothing()
    {
        $mockReturnNothing = $this->getMock('Geocoder\\HttpAdapter\\HttpAdapterInterface');
        $mockReturnNothing
            ->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue(''));
        $provider = new OpenStreetMapsProvider($mockReturnNothing);
        $provider->getReversedData(array('60.4539471728726', '22.2567841926781'));
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        $provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'fi-FI');
        $result   = $provider->getReversedData(array('60.4539471728726', '22.2567841926781'));

        $this->assertEquals(60.4539471768582, $result['latitude'], '', 0.0001);
        $this->assertEquals(22.2567842183875, $result['longitude'], '', 0.0001);
        $this->assertEquals(35, $result['streetNumber']);
        $this->assertEquals('Läntinen Pitkäkatu', $result['streetName']);
        $this->assertEquals(20100, $result['zipcode']);
        $this->assertEquals('Turku', $result['city']);
        $this->assertEquals('Varsinais-Suomi', $result['county']);
        $this->assertEquals(null, $result['region']);
        $this->assertEquals('Suomi', $result['country']);
        $this->assertEquals('FI', $result['countryCode']);
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://nominatim.openstreetmap.org/search?q=Hammm&format=xml&addressdetails=1
     */
    public function testGetGeocodedDataWithUnknownCity()
    {
        $provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $provider->getGeocodedData('Hammm');
    }

    public function testGetGeocodedDataWithCityDistrict()
    {
        $provider = new OpenStreetMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('Kalbacher Hauptstraße, 60437 Frankfurt, Germany');

        $this->assertNotNull('Kalbach', $result['cityDistrict']);
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

        $this->assertEquals(43.6189, $result['latitude'], '', 0.0001);
        $this->assertEquals(1.4564, $result['longitude'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Rue du Faubourg Bonnefoy', $result['streetName']);
        $this->assertEquals(31506, $result['zipcode']);
        $this->assertEquals(4, $result['cityDistrict']);
        $this->assertEquals('Toulouse', $result['city']);
        $this->assertEquals('Haute-Garonne', $result['county']);
        $this->assertEquals('Midi-Pyrénées', $result['region']);
        $this->assertEquals('France', $result['country']);
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
}
