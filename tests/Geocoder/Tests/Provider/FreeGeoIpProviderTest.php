<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\FreeGeoIpProvider;

class FreeGeoIpProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new FreeGeoIpProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('free_geo_ip', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The FreeGeoIpProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new FreeGeoIpProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The FreeGeoIpProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new FreeGeoIpProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The FreeGeoIpProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new FreeGeoIpProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new FreeGeoIpProvider($this->getMockAdapter($this->never()));
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

    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new FreeGeoIpProvider($this->getMockAdapter($this->never()));
        $result = $provider->getGeocodedData('::1');

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
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://freegeoip.net/json/74.200.247.59
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new FreeGeoIpProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://freegeoip.net/json/74.200.247.59
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new FreeGeoIpProvider($this->getMockAdapterReturns(''));
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new FreeGeoIpProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $provider->getGeocodedData('74.200.247.59');

        $this->assertEquals(33.0347, $result['latitude'], '', 0.0001);
        $this->assertEquals(-96.8134, $result['longitude'], '', 0.0001);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
    }

    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new FreeGeoIpProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $provider->getGeocodedData('::ffff:74.200.247.59');

        $this->assertEquals(33.0347, $result['latitude'], '', 0.0001);
        $this->assertEquals(-96.8134, $result['longitude'], '', 0.0001);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://freegeoip.net/json/::ffff:74.200.247.59
     */
    public function testGetGeocodedDataWithRealIPv6GetsNullContent()
    {
        $provider = new FreeGeoIpProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }

    public function testGetGeocodedDataWithUSIPv4()
    {
        $provider = new FreeGeoIpProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('74.200.247.59');

        $this->assertEquals('48', $result['regionCode']);
    }

    public function testGetGeocodedDataWithUSIPv6()
    {
        $provider = new FreeGeoIpProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $provider->getGeocodedData('::ffff:74.200.247.59');

        $this->assertEquals('48', $result['regionCode']);
    }

    public function testGetGeocodedDataWithUKIPv4()
    {
        $provider = new FreeGeoIpProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $provider->getGeocodedData('132.185.255.60');

        $this->assertEquals('H9', $result['regionCode']);
    }

    public function testGetGeocodedDataWithUKIPv6()
    {
        $provider = new FreeGeoIpProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $provider->getGeocodedData('::ffff:132.185.255.60');

        $this->assertEquals('H9', $result['regionCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The FreeGeoIpProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new FreeGeoIpProvider($this->getMockAdapter($this->never()));
        $provider->getReversedData(array(1, 2));
    }
}
