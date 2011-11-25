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
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetGeocodedDataWithNull()
    {
        $this->provider = new CloudMadeProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData(null);

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetGeocodedDataWithEmpty()
    {
        $this->provider = new CloudMadeProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetGeocodedDataWithLocalhost()
    {
        $this->provider = new CloudMadeProvider($this->getMockAdapter($this->never()), 'api_key');
        $result = $this->provider->getGeocodedData('127.0.0.1');

        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('zipcode', $result);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['country']);
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $this->provider = new CloudMadeProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $result = $this->provider->getGeocodedData('36 Quai des Orfèvres, Paris, France');

        $this->assertEquals(48.85645, $result['latitude']);
        $this->assertEquals(2.35243, $result['longitude']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertEquals('Ile-del-france', $result['region']);
        $this->assertEquals('France', $result['country']);
    }

    public function testGetReversedData()
    {
        $this->provider = new CloudMadeProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getReversedData(array(1, 2));

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $this->provider = new CloudMadeProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $result = $this->provider->getReversedData(array(48.85657, 2.35325));

        $this->assertEquals(48.85657, $result['latitude']);
        $this->assertEquals(2.35325, $result['longitude']);
        $this->assertNull($result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Ile-del-france', $result['region']);
        $this->assertEquals('France', $result['country']);
    }
}
