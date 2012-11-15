<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\IpInfoDbProvider;

class IpInfoDbProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new IpInfoDbProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('ip_info_db', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     */
    public function testGetDataWithNullApiKey()
    {
        $provider = new IpInfoDbProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IpInfoDbProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithRandomString()
    {
        $provider = new IpInfoDbProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('foobar');
    }

    /**
     * @expectedException Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IpInfoDbProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new IpInfoDbProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IpInfoDbProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new IpInfoDbProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IpInfoDbProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new IpInfoDbProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new IpInfoDbProvider($this->getMockAdapter($this->never()), 'api_key');
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
     * @expectedExceptionMessage The IpInfoDbProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new IpInfoDbProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://api.ipinfodb.com/v3/ip-city/?key=api_key&format=json&ip=74.125.45.100
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new IpInfoDbProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getGeocodedData('74.125.45.100');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://api.ipinfodb.com/v3/ip-city/?key=api_key&format=json&ip=74.125.45.100
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new IpInfoDbProvider($this->getMockAdapterReturns(''), 'api_key');
        $provider->getGeocodedData('74.125.45.100');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        if (!isset($_SERVER['IPINFODB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IPINFODB_API_KEY value in phpunit.xml');
        }

        $provider = new IpInfoDbProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['IPINFODB_API_KEY']);
        $result   = $provider->getGeocodedData('74.125.45.100');

        $this->assertEquals(37.3861, $result['latitude'], '', 0.0001);
        $this->assertEquals(-122.084, $result['longitude'], '', 0.0001);
        $this->assertEquals(94043, $result['zipcode']);
        $this->assertEquals('MOUNTAIN VIEW', $result['city']);
        $this->assertEquals('CALIFORNIA', $result['region']);
        $this->assertEquals('UNITED STATES', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertEquals('America/Los_Angeles', $result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IpInfoDbProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        if (!isset($_SERVER['IPINFODB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IPINFODB_API_KEY value in phpunit.xml');
        }

        $provider = new IpInfoDbProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), $_SERVER['IPINFODB_API_KEY']);
        $provider->getGeocodedData('::ffff:74.125.45.100');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IpInfoDbProvider is not able to do reverse geocoding.
     */
    public function testReversedData()
    {
        $provider = new IpInfoDbProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), 'api_key');
        $provider->getReversedData(array());
    }
}
