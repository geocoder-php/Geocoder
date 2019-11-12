<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IP2Location\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\IP2Location\IP2Location;

class IP2LocationTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName()
    {
        $provider = new IP2Location($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('ip2location', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IP2Location provider does not support street addresses, only IP addresses.
     */
    public function testGeocodeWithRandomString()
    {
        $provider = new IP2Location($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('foobar'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IP2Location provider does not support street addresses, only IP addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new IP2Location($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGeocodeWithInvalidKey()
    {
        $provider = new IP2Location($this->getHttpClient('invalid_key'), 'api_key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IP2Location provider does not support street addresses, only IP addresses.
     */
    public function testGeocodeWithInvalidIPAddress()
    {
        $provider = new IP2Location($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('300.23.255.5'));
    }

    public function testGeocodeWithRealIPv4()
    {
        if (!isset($_SERVER['IP2LOCATION_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IP2LOCATION_API_KEY value in phpunit.xml');
        }

        $provider = new IP2Location($this->getHttpClient($_SERVER['IP2LOCATION_API_KEY']), $_SERVER['IP2LOCATION_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.125.45.100'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(36.154, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-95.9928, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals(74101, $result->getPostalCode());
        $this->assertEquals('Tulsa', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Oklahoma', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    public function testGeocodeWithRealIPv6()
    {
        if (!isset($_SERVER['IP2LOCATION_API_KEY'])) {
            $this->markTestSkipped('You need to configure the IP2LOCATION_API_KEY value in phpunit.xml');
        }

        $provider = new IP2Location($this->getHttpClient($_SERVER['IP2LOCATION_API_KEY']), $_SERVER['IP2LOCATION_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.125.45.100'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(36.154, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-95.9928, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals(74101, $result->getPostalCode());
        $this->assertEquals('Tulsa', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Oklahoma', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IP2Location provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new IP2Location($this->getMockedHttpClient(), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
    }
}
