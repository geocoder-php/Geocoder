<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\IpGeoBaseProvider;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class IpGeoBaseProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('ip_geo_base', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IpGeoBaseProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IpGeoBaseProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IpGeoBaseProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapter($this->never()));
        $result   = $provider->getGeocodedData('127.0.0.1');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
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
     * @expectedExceptionMessage The IpGeoBaseProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapter($this->never()));
        $result = $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://ipgeobase.ru:7020/geo?ip=88.188.221.14
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://ipgeobase.ru:7020/geo?ip=88.188.221.14
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapterReturns(''));
        $provider->getGeocodedData('88.188.221.14');
    }

    public function testGetGeocodedDataWithRealIPv4Moscow()
    {
        $provider = new IpGeoBaseProvider($this->getAdapter());
        $result   = $provider->getGeocodedData('144.206.192.6');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(55.755787, $result['latitude'], '', 0.001);
        $this->assertEquals(37.617634, $result['longitude'], '', 0.001);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertEquals('Москва', $result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertEquals('Центральный федеральный округ', $result['cityDistrict']);
        $this->assertEquals('Москва', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['country']);
        $this->assertEquals('RU', $result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealIPv4Kiev()
    {
        $provider = new IpGeoBaseProvider($this->getAdapter());
        $result   = $provider->getGeocodedData('2.56.176.1');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(50.450001, $result['latitude'], '', 0.001);
        $this->assertEquals(30.523333, $result['longitude'], '', 0.001);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertEquals('Киев', $result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertEquals('Центральная Украина', $result['cityDistrict']);
        $this->assertEquals('Киев', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['country']);
        $this->assertEquals('UA', $result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IpGeoBaseProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new IpGeoBaseProvider($this->getAdapter());
        $provider->getGeocodedData('::ffff:88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The IpGeoBaseProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new IpGeoBaseProvider($this->getMockAdapter($this->never()));
        $provider->getReversedData(array(1, 2));
    }
}
