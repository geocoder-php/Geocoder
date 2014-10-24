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
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip does not support Street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new Geoip();
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip does not support Street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new Geoip();
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new Geoip();
        $results  = $provider->getGeocodedData('127.0.0.1');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('postalCode', $result);
        $this->assertArrayNotHasKey('timezone', $result);

        $this->assertEquals('localhost', $result['locality']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['county']);
        $this->assertEquals('localhost', $result['country']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new Geoip();
        $provider->getGeocodedData('::1');
    }

    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new Geoip();
        $results  = $provider->getGeocodedData('74.200.247.59');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
        $this->assertNotNull($result['latitude']);
        $this->assertNotNull($result['longitude']);
        $this->assertNotNull($result['postalCode']);
        $this->assertNotNull($result['locality']);
        $this->assertNotNull($result['regionCode']);
        $this->assertNotNull($result['region']);
        $this->assertNotNull($result['country']);
        $this->assertNotNull($result['countryCode']);
        $this->assertNotNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip does not support IPv6 addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new Geoip();
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new Geoip();
        $provider->getReversedData(array(1, 2));
    }
}
