<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;

use Geocoder\Provider\CloudMadeProvider;

class CloudMadeProviderTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new CloudMadeProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    public function testGetGeocodedData()
    {
        $this->provider = new CloudMadeProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('foobar');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['county']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
    }

    public function testGetGeocodedDataWithNull()
    {
        $this->provider = new CloudMadeProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData(null);

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['county']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
    }

    public function testGetGeocodedDataWithEmpty()
    {
        $this->provider = new CloudMadeProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['county']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
    }

    public function testGetGeocodedDataWithLocalhost()
    {
        $this->provider = new CloudMadeProvider($this->getMockAdapter($this->never()), 'api_key');
        $result = $this->provider->getGeocodedData('127.0.0.1');

        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('bounds', $result);
        $this->assertArrayNotHasKey('zipcode', $result);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['county']);
        $this->assertEquals('localhost', $result['country']);
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $this->provider = new CloudMadeProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $result = $this->provider->getGeocodedData('36 Quai des Orfèvres, Paris, France');

        $this->assertEquals(48.85645, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.35243, $result['longitude'], '', 0.0001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(48.70804, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(2.12785, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(49.00442, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(2.57701, $result['bounds']['east'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Paris', $result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertEquals('Ile-del-france', $result['region']);
        $this->assertEquals('Ile-del-france', $result['county']);
        $this->assertEquals('France', $result['country']);

        // not provided
        $this->assertNull($result['countryCode']);
    }

    public function testGetReversedData()
    {
        $this->provider = new CloudMadeProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getReversedData(array(1, 2));

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['county']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $this->provider = new CloudMadeProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $result = $this->provider->getReversedData(array(48.85657, 2.35325));

        $this->assertEquals(48.85657, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.35325, $result['longitude'], '', 0.0001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(48.85657, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(2.35325, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(48.85657, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(2.35325, $result['bounds']['east'], '', 0.0001);
        $this->assertEquals(5, $result['streetNumber']);
        $this->assertEquals('Rue Lobau', $result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Ile-del-france', $result['region']);
        $this->assertEquals('Ile-del-france', $result['county']);
        $this->assertEquals('France', $result['country']);

        // not provided
        $this->assertNull($result['countryCode']);
    }

    public function testGetGeocodedDataWithRealAddress2()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $this->provider = new CloudMadeProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $result = $this->provider->getGeocodedData('73 Boulevard Schuman, Clermont-Ferrand');

        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Boulevard Robert Schuman', $result['streetName']);
        $this->assertEquals('Clermont Ferrand', $result['city']);
        $this->assertEquals('Auvergne', $result['region']);
        $this->assertEquals('Auvergne', $result['county']);
        $this->assertEquals('France', $result['country']);

        // not provided
        $this->assertNull($result['countryCode']);
    }

    public function testGetGeocodedDataWithCityDistrict()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $this->provider = new CloudMadeProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $result = $this->provider->getGeocodedData('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');

        $this->assertNull($result['cityDistrict']);
    }
}
