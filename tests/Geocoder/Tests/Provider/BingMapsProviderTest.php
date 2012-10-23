<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\BingMapsProvider;

class BingMapsProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new BingMapsProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('bing_maps', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new BingMapsProvider($this->getMock('Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://dev.virtualearth.net/REST/v1/Locations/?q=foobar&key=api_key
     */
    public function testGetGeocodedDataWithInvalidData()
    {
        $provider = new BingMapsProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://dev.virtualearth.net/REST/v1/Locations/?q=&key=api_key
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new BingMapsProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://dev.virtualearth.net/REST/v1/Locations/?q=&key=api_key
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new BingMapsProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The BingMapsProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new BingMapsProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The BingMapsProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new BingMapsProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://dev.virtualearth.net/REST/v1/Locations/?q=10+avenue+Gambetta%2C+Paris%2C+France&key=api_key
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new BingMapsProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['BINGMAPS_API_KEY'], 'fr-FR');
        $result   = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertEquals(48.86321675999999, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.3887721299999995, $result['longitude'], '', 0.0001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(48.859354042429, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(2.3809438666389, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(48.867079477571, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(2.3966003933611, $result['bounds']['east'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('10 Avenue Gambetta', $result['streetName']);
        $this->assertEquals(75020, $result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('IdF', $result['region']);
        $this->assertEquals('France', $result['country']);

        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://dev.virtualearth.net/REST/v1/Locations/1.000000,2.000000?key=api_key
     */
    public function testGetReversedData()
    {
        $provider = new BingMapsProvider($this->getMockAdapter(), 'api_key');
        $provider->getReversedData(array(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://dev.virtualearth.net/REST/v1/Locations/48.863216,2.388772?key=api_key
     */
    public function testGetReversedDataWithCoordinatesContentReturnNull()
    {
        $provider = new BingMapsProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getReversedData(array(48.86321648955345, 2.3887719959020615));
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['BINGMAPS_API_KEY']);
        $result = $provider->getReversedData(array(48.86321648955345, 2.3887719959020615));

        $this->assertEquals(48.86321648955345, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.3887719959020615, $result['longitude'], '', 0.0001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(48.859353771983, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(2.3809437325833, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(48.867079207124, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(2.3966002592208, $result['bounds']['east'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('10 Avenue Gambetta', $result['streetName']);
        $this->assertEquals(75020, $result['zipcode']);
        // $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('20e Arrondissement', $result['city']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('IdF', $result['region']);
        $this->assertEquals('France', $result['country']);

        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithCity()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['BINGMAPS_API_KEY']);
        $result = $provider->getGeocodedData('Hannover');

        $this->assertNull($result['zipcode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithCityDistrict()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['BINGMAPS_API_KEY']);
        $result = $provider->getGeocodedData('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');

        $this->assertNull($result['cityDistrict']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The BingMapsProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv4()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['BINGMAPS_API_KEY']);
        $result = $provider->getGeocodedData('88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The BingMapsProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $provider = new BingMapsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['BINGMAPS_API_KEY']);
        $result = $provider->getGeocodedData('::ffff:88.188.221.14');
    }
}
