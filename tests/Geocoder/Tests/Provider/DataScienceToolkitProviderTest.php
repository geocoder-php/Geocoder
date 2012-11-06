<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\DataScienceToolkitProvider;

class DataScienceToolkitProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new DataScienceToolkitProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('data_science_toolkit', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The DataScienceToolkitProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new DataScienceToolkitProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The DataScienceToolkitProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new DataScienceToolkitProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The DataScienceToolkitProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new DataScienceToolkitProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('10 rue de baraban lyon');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new DataScienceToolkitProvider($this->getMockAdapter($this->never()));
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
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://www.datasciencetoolkit.org/ip2coordinates/81.220.239.218
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new DataScienceToolkitProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('81.220.239.218');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://www.datasciencetoolkit.org/ip2coordinates/81.220.239.218
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new DataScienceToolkitProvider($this->getMockAdapterReturns(''));
        $provider->getGeocodedData('81.220.239.218');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The DataScienceToolkitProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new DataScienceToolkitProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $provider->getGeocodedData('::ffff:88.188.221.14');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new DataScienceToolkitProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('81.220.239.218');

        $this->assertEquals(45.75 , $result['latitude'], '', 0.0001);
        $this->assertEquals(4.8499999046326, $result['longitude'], '', 0.0001);
        $this->assertEquals('Lyon', $result['city']);
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The DataScienceToolkitProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new DataScienceToolkitProvider($this->getMockAdapter($this->never()));
        $provider->getReversedData(array(1, 2));
    }
}
