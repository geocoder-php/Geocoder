<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;

use Geocoder\Provider\IpInfoDbProvider;

class IpInfoDbProviderTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testGetDataWithNullApiKey()
    {
        $provider = new IpInfoDbProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    public function testGetGeocodedDataWithoutAdapter()
    {
        $this->provider = new IpInfoDbProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('foobar');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
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
        $this->provider = new IpInfoDbProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData(null);

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
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
        $this->provider = new IpInfoDbProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['county']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
    }

    public function testGetGeocodedDataWithAddress()
    {
        $this->provider = new IpInfoDbProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
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
        $this->provider = new IpInfoDbProvider($this->getMockAdapter($this->never()), 'api_key');
        $result = $this->provider->getGeocodedData('127.0.0.1');

        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('zipcode', $result);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['county']);
        $this->assertEquals('localhost', $result['country']);
    }

    public function testGetGeocodedDataWithRealIp()
    {
        if (!isset($_SERVER['IPINFODB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IPINFODB_API_KEY value in phpunit.xml');
        }

        $this->provider = new IpInfoDbProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter(), $_SERVER['IPINFODB_API_KEY']);
        $result = $this->provider->getGeocodedData('74.200.247.59');

        $this->assertEquals(33.0404, $result['latitude'], '', 0.0001);
        $this->assertEquals(-96.7238, $result['longitude'], '', 0.0001);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('PLANO', $result['city']);
        $this->assertEquals('TEXAS', $result['region']);
        $this->assertEquals('UNITED STATES', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     */
    public function testReversedData()
    {
        $this->provider = new IpInfoDbProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), 'api_key');
        $result = $this->provider->getReversedData(array());
    }
}
