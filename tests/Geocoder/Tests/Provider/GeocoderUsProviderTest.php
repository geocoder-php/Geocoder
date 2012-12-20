<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeocoderUsProvider;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class GeocoderUsProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new GeocoderUsProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('geocoder_us', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocoder.us/service/rest/?address=1600+Pennsylvania+Ave%2C+Washington%2C+DC
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new GeocoderUsProvider($this->getMockAdapter());
        $provider->getGeocodedData('1600 Pennsylvania Ave, Washington, DC');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocoder.us/service/rest/?address=foobar
     */
    public function testGetGeocodedDataWithWrongAddress()
    {
        $this->markTestIncomplete('Timeout too long');

        $provider = new GeocoderUsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $provider->getGeocodedData('foobar');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $this->markTestIncomplete('Timeout too long');

        $provider = new GeocoderUsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('1600 Pennsylvania Ave, Washington, DC');

        $this->assertEquals(38.898748, $result['latitude'], '', 0.0001);
        $this->assertEquals(-77.037684, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeocoderUsProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new GeocoderUsProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeocoderUsProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new GeocoderUsProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeocoderUsProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv4()
    {
        $provider = new GeocoderUsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeocoderUsProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv6()
    {
        $provider = new GeocoderUsProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeocoderUsProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new GeocoderUsProvider($this->getMockAdapter($this->never()));
        $provider->getReversedData(array(1, 2));
    }
}
