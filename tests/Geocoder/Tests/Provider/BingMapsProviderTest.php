<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;

use Geocoder\Provider\BingMapsProvider;

class BingMapsProviderTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new BingMapsProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    public function testGetGeocodedData()
    {
        $this->provider = new BingMapsProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('foobar');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['county']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
    }

    public function testGetGeocodedDataWithNull()
    {
        $this->provider = new BingMapsProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData(null);

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['county']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
    }

    public function testGetGeocodedDataWithEmpty()
    {
        $this->provider = new BingMapsProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['county']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
    }

    public function testGetGeocodedDataWithLocalhost()
    {
        $this->provider = new BingMapsProvider($this->getMockAdapter($this->never()), 'api_key');
        $result = $this->provider->getGeocodedData('127.0.0.1');

        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('bounds', $result);
        $this->assertArrayNotHasKey('zipcode', $result);
        $this->assertArrayNotHasKey('streetNumber', $result);
        $this->assertArrayNotHasKey('streetName', $result);
        $this->assertArrayNotHasKey('countryCode', $result);
        $this->assertArrayNotHasKey('countryCode', $result);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['country']);
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $this->provider = new BingMapsProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['BINGMAPS_API_KEY']);
        $result = $this->provider->getGeocodedData('10 avenue Gambetta, Paris, France');

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
    }

    public function testGetReversedData()
    {
        $this->provider = new BingMapsProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getReversedData(array(1, 2));

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['county']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $this->provider = new BingMapsProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['BINGMAPS_API_KEY']);
        $result = $this->provider->getReversedData(array(48.86321648955345, 2.3887719959020615));

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
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('IdF', $result['region']);
        $this->assertEquals('France', $result['country']);

        $this->assertNull($result['countryCode']);
    }

    public function testGetGeocodedDataWithCity()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $this->provider = new BingMapsProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['BINGMAPS_API_KEY']);
        $result = $this->provider->getGeocodedData('Hannover');

        $this->assertNull($result['zipcode']);
    }

    public function testGetGeocodedDataWithCityDistrict()
    {
        if (!isset($_SERVER['BINGMAPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the BINGMAPS_API_KEY value in phpunit.xml');
        }

        $this->provider = new BingMapsProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['BINGMAPS_API_KEY']);
        $result = $this->provider->getGeocodedData('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');

        $this->assertNull($result['cityDistrict']);
    }
}
