<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\YahooProvider;

class YahooProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new YahooProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('yahoo', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage No API Key provided
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new YahooProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://where.yahooapis.com/geocode?q=foobar&flags=JXT&appid=api_key
     */
    public function testGetGeocodedDataWithRandomString()
    {
        $provider = new YahooProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('foobar');
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://where.yahooapis.com/geocode?q=&flags=JXT&appid=api_key
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new YahooProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://where.yahooapis.com/geocode?q=&flags=JXT&appid=api_key
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new YahooProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new YahooProvider($this->getMockAdapter($this->never()), 'api_key');
        $result   = $provider->getGeocodedData('127.0.0.1');

        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('bounds', $result);
        $this->assertArrayNotHasKey('zipcode', $result);
        $this->assertArrayNotHasKey('timezone', $result);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['country']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The YahooProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new YahooProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://where.yahooapis.com/geocode?q=74.200.247.59&flags=JXT&appid=api_key
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new YahooProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        $this->markTestIncomplete('Expected data changed');

        if (!isset($_SERVER['YAHOO_API_KEY'])) {
            $this->markTestSkipped('You need to configure the YAHOO_API_KEY value in phpunit.xml');
        }

        $provider = new YahooProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['YAHOO_API_KEY']);
        $result   = $provider->getGeocodedData('74.200.247.59');

        $this->assertEquals(33.036711, $result['latitude'], '', 0.0001);
        $this->assertEquals(-96.813541, $result['longitude'], '', 0.0001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(33.007820, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(-96.860229, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(33.065601, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(-96.766853, $result['bounds']['east'], '', 0.0001);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals('Collin County', $result['county']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertEquals('America/Chicago', $result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The YahooProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new YahooProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'api_key');
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://where.yahooapis.com/geocode?q=10+avenue+Gambetta%2C+Paris%2C+France&flags=JXT&appid=api_key
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new YahooProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['YAHOO_API_KEY'])) {
            $this->markTestSkipped('You need to configure the YAHOO_API_KEY value in phpunit.xml');
        }

        $provider = new YahooProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['YAHOO_API_KEY']);
        $result   = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertEquals(48.863217, $result['latitude'], '', 0.01);
        $this->assertEquals(2.388821, $result['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(48.863217, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(2.388821, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(48.863217, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(2.388821, $result['bounds']['east'], '', 0.01);
        $this->assertEquals(10, $result['streetNumber']);
        $this->assertEquals('Avenue Gambetta', $result['streetName']);
        $this->assertEquals(75020, $result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('Ile-de-France', $result['region']);
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
        $this->assertEquals('Europe/Paris', $result['timezone']);
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://where.yahooapis.com/geocode?q=1.000000,+2.000000&gflags=RT&flags=JX&appid=api_key
     */
    public function testGetReversedDataWithBadCoordinates()
    {
        $provider = new YahooProvider($this->getMockAdapter(), 'api_key');
        $provider->getReversedData(array(1, 2));
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['YAHOO_API_KEY'])) {
            $this->markTestSkipped('You need to configure the YAHOO_API_KEY value in phpunit.xml');
        }

        $provider = new YahooProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['YAHOO_API_KEY']);
        $result   = $provider->getReversedData(array(33.036711, -96.813541));

        $this->assertEquals(33.036711, $result['latitude'], '', 0.01);
        $this->assertEquals(-96.813541, $result['longitude'], '', 0.01);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(33.036711, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(-96.813541, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(33.036711, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(-96.813541, $result['bounds']['east'], '', 0.01);
        $this->assertEquals(5529, $result['streetNumber']);
        $this->assertEquals('Weatherby Ln', $result['streetName']);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals('Collin County', $result['county']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);

        // not provided
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithCityDistrict()
    {
        if (!isset($_SERVER['YAHOO_API_KEY'])) {
            $this->markTestSkipped('You need to configure the YAHOO_API_KEY value in phpunit.xml');
        }

        $provider = new YahooProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['YAHOO_API_KEY']);
        $result   = $provider->getGeocodedData('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');

        $this->assertEquals('Kalbach', $result['cityDistrict']);
    }
}
