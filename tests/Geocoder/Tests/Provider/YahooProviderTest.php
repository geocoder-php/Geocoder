<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;

use Geocoder\Provider\YahooProvider;

class YahooProviderTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new YahooProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    public function testGetGeocodedData()
    {
        $this->provider = new YahooProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('foobar');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetGeocodedDataWithNull()
    {
        $this->provider = new YahooProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData(null);

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['county']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetGeocodedDataWithEmpty()
    {
        $this->provider = new YahooProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['county']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetGeocodedDataWithLocalhost()
    {
        $this->provider = new YahooProvider($this->getMockAdapter($this->never()), 'api_key');
        $result = $this->provider->getGeocodedData('127.0.0.1');

        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('zipcode', $result);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['country']);
    }

    public function testGetGeocodedDataWithRealIp()
    {
        if (!isset($_SERVER['YAHOO_API_KEY'])) {
            $this->markTestSkipped('You need to configure the YAHOO_API_KEY value in phpunit.xml');
        }

        $this->provider = new YahooProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['YAHOO_API_KEY']);
        $result = $this->provider->getGeocodedData('74.200.247.59');

        $this->assertEquals(33.036711, $result['latitude']);
        $this->assertEquals(-96.813541, $result['longitude']);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('United States', $result['country']);
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['YAHOO_API_KEY'])) {
            $this->markTestSkipped('You need to configure the YAHOO_API_KEY value in phpunit.xml');
        }

        $this->provider = new YahooProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['YAHOO_API_KEY']);
        $result = $this->provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertEquals(48.863217, $result['latitude']);
        $this->assertEquals(2.388821, $result['longitude']);
        $this->assertEquals(75020, $result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('Île-de-France', $result['region']);
        $this->assertEquals('France', $result['country']);
    }

    public function testGetReversedData()
    {
        $this->provider = new YahooProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getReversedData(array(1, 2));

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['county']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['YAHOO_API_KEY'])) {
            $this->markTestSkipped('You need to configure the YAHOO_API_KEY value in phpunit.xml');
        }

        $this->provider = new YahooProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['YAHOO_API_KEY']);
        $result = $this->provider->getReversedData(array(33.036711, -96.813541));

        $this->assertEquals(33.036711, $result['latitude']);
        $this->assertEquals(-96.813541, $result['longitude']);
        //$this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('United States', $result['country']);
    }
}
