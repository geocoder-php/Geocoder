<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\HostIp;

class HostIpTest extends TestCase
{
    public function testGetName()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $this->assertEquals('host_ip', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider does not support Street addresses.
     */
    public function testGeocodeWithNull()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider does not support Street addresses.
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider does not support Street addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $results  = $provider->geocode('127.0.0.1');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNull($result->getLatitude());
        $this->assertNull($result->getLongitude());
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getTimezone());
        $this->assertEmpty($result->getAdminLevels());

        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider does not support IPv6 addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://api.hostip.info/get_json.php?ip=88.188.221.14&position=true".
     */
    public function testGeocodeWithRealIPv4GetsNullContent()
    {
        $provider = new HostIp($this->getMockAdapterReturns(null));
        $provider->geocode('88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://api.hostip.info/get_json.php?ip=88.188.221.14&position=true".
     */
    public function testGeocodeWithRealIPv4GetsEmptyContent()
    {
        $provider = new HostIp($this->getMockAdapterReturns(''));
        $provider->geocode('88.188.221.14');
    }

    public function testGeocodeWithRealIPv4()
    {
        $provider = new HostIp($this->getAdapter());
        $results  = $provider->geocode('88.188.221.14');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(45.5333, $result->getLatitude(), '', 0.0001);
        $this->assertEquals(2.6167, $result->getLongitude(), '', 0.0001);
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Aulnat', $result->getLocality());
        $this->assertEmpty($result->getAdminLevels());
        $this->assertEquals('FRANCE', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider does not support IPv6 addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = new HostIp($this->getAdapter());
        $provider->geocode('::ffff:88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The HostIp provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new HostIp($this->getMockAdapter($this->never()));
        $provider->reverse(1, 2);
    }

    public function testGeocodeWithAnotherIp()
    {
        $provider = new HostIp($this->getAdapter());
        $results  = $provider->geocode('33.33.33.22');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNull($result->getLatitude());
        $this->assertNull($result->getLongitude());
    }
}
