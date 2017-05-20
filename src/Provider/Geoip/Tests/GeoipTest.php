<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Geoip\Tests;

use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Tests\TestCase;
use Geocoder\Provider\Geoip\Geoip;

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
        $provider->geocodeQuery(GeocodeQuery::create(null));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip provider does not support street addresses, only IPv4 addresses.
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new Geoip();
        $provider->geocodeQuery(GeocodeQuery::create(''));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip provider does not support street addresses, only IPv4 addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new Geoip();
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new Geoip();
        $results = $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertNull($result->getCoordinates());

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
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip provider does not support IPv6 addresses, only IPv4 addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = new Geoip();
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Geoip provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new Geoip();
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }
}
