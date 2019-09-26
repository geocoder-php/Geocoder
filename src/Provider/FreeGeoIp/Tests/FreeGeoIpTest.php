<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\FreeGeoIp\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Provider\FreeGeoIp\FreeGeoIp;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class FreeGeoIpTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName()
    {
        $provider = $this->getProvider();
        $this->assertEquals('free_geo_ip', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The FreeGeoIp provider does not support street addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = $this->getProvider();
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('::1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    public function testGeocodeWithRealIPv4()
    {
        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(37.751, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-97.822, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    public function testGeocodeWithRealIPv6()
    {
        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);

        $this->assertEquals(37.751, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-97.822, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    public function testGeocodeWithUSIPv4()
    {
        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('72.229.28.185'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);

        $this->assertEquals(40.7263, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-73.9819, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('New York', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('NY', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('10009', $result->getPostalCode());
        $this->assertEquals('America/New_York', $result->getTimezone());
    }

    public function testGeocodeWithUSIPv6()
    {
        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);

        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    public function testGeocodeWithUKIPv4()
    {
        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('129.67.242.154'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);
        $this->assertEquals('GB', $results->first()->getCountry()->getCode());

        $this->assertCount(1, $results->first()->getAdminLevels());
        $this->assertEquals('ENG', $results->first()->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Oxford', $results->first()->getLocality());
    }

    public function testGeocodeWithUKIPv6()
    {
        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('::ffff:129.67.242.154'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);
        $this->assertEquals('GB', $results->first()->getCountry()->getCode());
    }

    public function testGeocodeWithRuLocale()
    {
        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('81.27.51.253')->withLocale('ru'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);
        $this->assertEquals('Владимирская область', $results->first()->getAdminLevels()->first()->getName());
        $this->assertEquals('Владимир', $results->first()->getLocality());
        $this->assertEquals('Россия', $results->first()->getCountry()->getName());
    }

    public function testGeocodeWithFrLocale()
    {
        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('81.27.51.252')->withLocale('fr'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);
        $this->assertEquals('Oblast de Vladimir', $results->first()->getAdminLevels()->first()->getName());
        $this->assertEquals('Vladimir', $results->first()->getLocality());
        $this->assertEquals('Russie', $results->first()->getCountry()->getName());
    }

    public function testGeocodeWithIncorrectLocale()
    {
        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('81.27.51.251')->withLocale('wrong_locale'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);
        $this->assertEquals('Vladimirskaya Oblast\'', $results->first()->getAdminLevels()->first()->getName());
        $this->assertEquals('Vladimir', $results->first()->getLocality());
        $this->assertEquals('Russia', $results->first()->getCountry()->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The FreeGeoIp provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = $this->getProvider();
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testServerEmptyResponse()
    {
        $provider = new FreeGeoIp($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('87.227.124.53'));
    }

    private function getProvider(): FreeGeoIp
    {
        return new FreeGeoIp($this->getHttpClient('api_key'));
    }
}
