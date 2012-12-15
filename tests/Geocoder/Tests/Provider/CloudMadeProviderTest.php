<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\CloudMadeProvider;

class CloudMadeProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new CloudMadeProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('cloudmade', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new CloudMadeProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocoding.cloudmade.com/api_key/geocoding/v2/find.js?query=foobar&distance=closest&return_location=true&results=1
     */
    public function testGetGeocodedData()
    {
        $provider = new CloudMadeProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocoding.cloudmade.com/api_key/geocoding/v2/find.js?query=&distance=closest&return_location=true&results=1
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new CloudMadeProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocoding.cloudmade.com/api_key/geocoding/v2/find.js?query=&distance=closest&return_location=true&results=1
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new CloudMadeProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The CloudMadeProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new CloudMadeProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The CloudMadeProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new CloudMadeProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocoding.cloudmade.com/api_key/geocoding/v2/find.js?query=36+Quai+des+Orf%C3%A8vres%2C+Paris%2C+France&distance=closest&return_location=true&results=1
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new CloudMadeProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getGeocodedData('36 Quai des Orfèvres, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage Invalid API Key invalid_key
     */
    public function testGetGeocodedDataWithInvalidApiKey()
    {
        $provider = new CloudMadeProvider($this->getMockAdapterReturns('Forbidden request'), 'invalid_key');
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage Invalid API Key invalid_key
     */
    public function testGetGeocodedDataWithRealInvalidApiKey()
    {
        $provider = new CloudMadeProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'invalid_key');
        $provider->getGeocodedData('foo');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new CloudMadeProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $result = $provider->getGeocodedData('36 Quai des Orfèvres, Paris, France');

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
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocoding.cloudmade.com/api_key/geocoding/v2/find.js?around=1.000000,2.000000&object_type=address&return_location=true&results=1
     */
    public function testGetReversedData()
    {
        $provider = new CloudMadeProvider($this->getMockAdapter(), 'api_key');
        $provider->getReversedData(array(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocoding.cloudmade.com/api_key/geocoding/v2/find.js?around=48.856570,2.353250&object_type=address&return_location=true&results=1
     */
    public function testGetReversedDataWithCoordinatesGetsNullContent()
    {
        $provider = new CloudMadeProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getReversedData(array(48.85657, 2.35325));
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new CloudMadeProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $result = $provider->getReversedData(array(48.85657, 2.35325));

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
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddress2()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new CloudMadeProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $result = $provider->getGeocodedData('73 Boulevard Schuman, Clermont-Ferrand');

        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Boulevard Robert Schuman', $result['streetName']);
        $this->assertEquals('Clermont Ferrand', $result['city']);
        $this->assertEquals('Auvergne', $result['region']);
        $this->assertEquals('Auvergne', $result['county']);
        $this->assertEquals('France', $result['country']);

        // not provided
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithCityDistrict()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new CloudMadeProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $result = $provider->getGeocodedData('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');

        $this->assertNull($result['cityDistrict']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The CloudMadeProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv4()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new CloudMadeProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $result = $provider->getGeocodedData('88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The CloudMadeProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new CloudMadeProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $result = $provider->getGeocodedData('::ffff:88.188.221.14');
    }
}
