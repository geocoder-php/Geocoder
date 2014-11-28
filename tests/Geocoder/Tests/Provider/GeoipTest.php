<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\Geoip;

class GeoipTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        if (!function_exists('geoip_record_by_name')) {
            $this->markTestSkipped('You have to install GeoIP.');
        }
    }

    public function testGetName()
    {
        $provider = new Geoip();
        $this->assertEquals('geoip', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip does not support Street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new Geoip();
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip does not support Street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new Geoip();
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip does not support Street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new Geoip();
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new Geoip();
        $results  = $provider->geocode('127.0.0.1');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNull($result->getLatitude());
        $this->assertNull($result->getLongitude());
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getTimezone());
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCounty());
        $this->assertEquals('localhost', $result->getRegion());
        $this->assertEquals('localhost', $result->getCountry());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new Geoip();
        $provider->geocode('::1');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new Geoip();
        $results  = $provider->geocode('74.200.247.59');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNotNull($result->getLatitude());
        $this->assertNotNull($result->getLongitude());
        $this->assertNotNull($result->getPostalCode());
        $this->assertNotNull($result->getLocality());
        $this->assertNotNull($result->getRegion());
        $this->assertNotNull($result->getRegionCode());
        $this->assertNotNull($result->getCountry());
        $this->assertNotNull($result->getCountryCode());
        $this->assertNotNull($result->getTimezone());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new Geoip();
        $provider->geocode('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new Geoip();
        $provider->reverse(1, 2);
    }
}
