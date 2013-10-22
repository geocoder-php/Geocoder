<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeoipProvider;

class GeoipProviderTest extends TestCase
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
        $provider = new GeoipProvider();
        $this->assertEquals('geoip', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoipProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new GeoipProvider();
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoipProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new GeoipProvider();
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoipProvider does not support Street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new GeoipProvider();
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new GeoipProvider();
        $results  = $provider->getGeocodedData('127.0.0.1');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
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
     * @expectedExceptionMessage The GeoipProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new GeoipProvider();
        $provider->getGeocodedData('::1');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new GeoipProvider();
        $results  = $provider->getGeocodedData('74.200.247.59');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
        $this->assertNotNull($result['latitude']);
        $this->assertNotNull($result['longitude']);
        $this->assertNotNull($result['zipcode']);
        $this->assertNotNull($result['city']);
        $this->assertNotNull($result['regionCode']);
        $this->assertNotNull($result['region']);
        $this->assertNotNull($result['country']);
        $this->assertNotNull($result['countryCode']);
        $this->assertNotNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoipProvider does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new GeoipProvider();
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The GeoipProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new GeoipProvider();
        $provider->getReversedData(array(1, 2));
    }
}
