<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IpInfoDb\Tests;

use Geocoder\Location;
use Geocoder\Model\Query\GeocodeQuery;
use Geocoder\Model\Query\ReverseQuery;
use Geocoder\Tests\TestCase;
use Geocoder\Provider\IpInfoDb\IpInfoDb;

class IpInfoDbTest extends TestCase
{
    /**
     * @expectedException \Geocoder\Exception\InvalidArgument
     * @expectedExceptionMessage Invalid precision value "foo" (allowed values: "city", "country").
     */
    public function testConstructWithInvalidPrecision()
    {
        new IpInfoDb($this->getMockAdapter($this->never()), 'api_key', 'foo');
    }

    public function testGetName()
    {
        $provider = new IpInfoDb($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('ip_info_db', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     */
    public function testGetDataWithNullApiKey()
    {
        $provider = new IpInfoDb($this->getMockBuilder('Http\Client\HttpClient')->getMock(), null);
        $provider->geocodeQuery(GeocodeQuery::create('foo'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfoDb provider does not support street addresses, only IPv4 addresses.
     */
    public function testGeocodeWithRandomString()
    {
        $provider = new IpInfoDb($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('foobar'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfoDb provider does not support street addresses, only IPv4 addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new IpInfoDb($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new IpInfoDb($this->getMockAdapter($this->never()), 'api_key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNull($result->getCoordinates());

        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getTimezone());
        $this->assertEmpty($result->getAdminLevels());

        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfoDb provider does not support IPv6 addresses, only IPv4 addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new IpInfoDb($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\ZeroResults
     */
    public function testGeocodeWithRealIPv4GetsNullContent()
    {
        $provider = new IpInfoDb($this->getMockAdapterReturns(null), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));
    }

    /**
     * @expectedException \Geocoder\Exception\ZeroResults
     */
    public function testGeocodeWithRealIPv4GetsEmptyContent()
    {
        $provider = new IpInfoDb($this->getMockAdapterReturns(''), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));
    }

    public function testGeocodeWithRealIPv4()
    {
        if (!isset($_SERVER['IPINFODB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IPINFODB_API_KEY value in phpunit.xml');
        }

        $provider = new IpInfoDb($this->getAdapter($_SERVER['IPINFODB_API_KEY']), $_SERVER['IPINFODB_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(37.406, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-122.079, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals(94043, $result->getPostalCode());
        $this->assertEquals('Mountain View', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('California', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('America/Los_Angeles', $result->getTimezone());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfoDb provider does not support IPv6 addresses, only IPv4 addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        if (!isset($_SERVER['IPINFODB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IPINFODB_API_KEY value in phpunit.xml');
        }

        $provider = new IpInfoDb($this->getAdapter($_SERVER['IPINFODB_API_KEY']), $_SERVER['IPINFODB_API_KEY']);
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.125.45.100'));
    }

    /**
     * @group temp
     */
    public function testGetGeocodedDataWithCountryPrecision()
    {
        if (!isset($_SERVER['IPINFODB_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IPINFODB_API_KEY value in phpunit.xml');
        }

        $provider = new IpInfoDb($this->getAdapter($_SERVER['IPINFODB_API_KEY']), $_SERVER['IPINFODB_API_KEY'], 'country');
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNull($result->getCoordinates());

        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getLocality());
        $this->assertEmpty($result->getAdminLevels());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfoDb provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new IpInfoDb($this->getMockBuilder('Http\Client\HttpClient')->getMock(), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(null, null));
    }
}
