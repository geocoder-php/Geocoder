<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IpInfo\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\IpInfo\IpInfo;

class IpInfoTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName()
    {
        $provider = new IpInfo($this->getMockedHttpClient());
        $this->assertEquals('ip_info', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfo provider does not support street addresses, only IP addresses.
     */
    public function testGeocodeWithRandomString()
    {
        $provider = new IpInfo($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('foobar'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfo provider does not support street addresses, only IP addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new IpInfo($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    /** @dataProvider provideLocalhostIps */
    public function testGeocodeWithLocalhost($localhostIp)
    {
        $provider = new IpInfo($this->getMockedHttpClient());
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

    public function provideLocalhostIps()
    {
        yield ['127.0.0.1'];
        yield ['::1'];
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocodeWithRealIPv4GetsNullContent()
    {
        $provider = new IpInfo($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));
    }

    public function testGeocodeWithRealIPv4()
    {
        $provider = new IpInfo($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(36.154, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-95.9928, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals(74102, $result->getPostalCode());
        $this->assertEquals('Tulsa', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Oklahoma', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealIPv6()
    {
        $provider = new IpInfo($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('2601:9:7680:363:75df:f491:6f85:352f'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(39.934, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-74.891, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals('08054', $result->getPostalCode());
        $this->assertEquals('Mount Laurel', $result->getLocality());
        $this->assertNull($result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpInfo provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new IpInfo($this->getMockedHttpClient());
        $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
    }
}
