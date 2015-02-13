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
     * @expectedExceptionMessage The Geoip provider does not support street addresses, only IPv4 addresses.
     */
    public function testGeocodeWithNull()
    {
        $provider = new Geoip();
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip provider does not support street addresses, only IPv4 addresses.
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new Geoip();
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip provider does not support street addresses, only IPv4 addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new Geoip();
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new Geoip();
        $results  = $provider->geocode('127.0.0.1');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertNull($result->getLatitude());
        $this->assertNull($result->getLongitude());
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getTimezone());
        $this->assertEmpty($result->getAdminLevels());
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertNotNull($result->getCountry());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip provider does not support IPv6 addresses, only IPv4 addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new Geoip();
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip provider does not support IPv6 addresses, only IPv4 addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = new Geoip();
        $provider->geocode('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new Geoip();
        $provider->reverse(1, 2);
    }
}
