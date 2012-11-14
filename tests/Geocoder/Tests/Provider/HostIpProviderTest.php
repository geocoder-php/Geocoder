<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\HostIpProvider;

class HostIpProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new HostIpProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('host_ip', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The HostIpProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new HostIpProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The HostIpProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new HostIpProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The HostIpProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new HostIpProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new HostIpProvider($this->getMockAdapter($this->never()));
        $result = $provider->getGeocodedData('127.0.0.1');

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
     * @expectedExceptionMessage The HostIpProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new HostIpProvider($this->getMockAdapter($this->never()));
        $result = $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://api.hostip.info/get_xml.php?ip=88.188.221.14&position=true
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new HostIpProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://api.hostip.info/get_xml.php?ip=88.188.221.14&position=true
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new HostIpProvider($this->getMockAdapterReturns(''));
        $provider->getGeocodedData('88.188.221.14');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new HostIpProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('88.188.221.14');

        $this->assertEquals(45.5333, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.6167, $result['longitude'], '', 0.0001);
        $this->assertNull($result['zipcode']);
        $this->assertEquals('Aulnat', $result['city']);
        $this->assertNull($result['region']);
        $this->assertEquals('FRANCE', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The HostIpProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new HostIpProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $provider->getGeocodedData('::ffff:88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The HostIpProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new HostIpProvider($this->getMockAdapter($this->never()));
        $provider->getReversedData(array(1, 2));
    }

    public function testGetGeocodedDataWithAnotherIp()
    {
        $provider = new HostIpProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $provider->getGeocodedData('33.33.33.22');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
    }
}
