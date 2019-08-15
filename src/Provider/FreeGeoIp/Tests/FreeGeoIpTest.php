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
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\FreeGeoIp\FreeGeoIp;

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
        $this->markTestSkipped('Web service no longer operating');

        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(33.0347, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-96.8134, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals(75093, $result->getPostalCode());
        $this->assertEquals('Plano', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Texas', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    public function testGeocodeWithRealIPv6()
    {
        $this->markTestSkipped('Web service no longer operating');

        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(33.0347, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-96.8134, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals(75093, $result->getPostalCode());
        $this->assertEquals('Plano', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Texas', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    public function testGeocodeWithUSIPv4()
    {
        $this->markTestSkipped('Web service no longer operating');

        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        $this->assertCount(1, $results->first()->getAdminLevels());
        $this->assertEquals('TX', $results->first()->getAdminLevels()->get(1)->getCode());
    }

    public function testGeocodeWithUSIPv6()
    {
        $this->markTestSkipped('Web service no longer operating');

        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        $this->assertCount(1, $results->first()->getAdminLevels());
        $this->assertEquals('TX', $results->first()->getAdminLevels()->get(1)->getCode());
    }

    public function testGeocodeWithUKIPv4()
    {
        $this->markTestSkipped('Web service no longer operating');

        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('129.67.242.154'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);
        $this->assertEquals('GB', $results->first()->getCountry()->getCode());

        $this->assertCount(1, $results->first()->getAdminLevels());
        $this->assertEquals('ENG', $results->first()->getAdminLevels()->get(1)->getCode());
    }

    public function testGeocodeWithUKIPv6()
    {
        $this->markTestSkipped('Web service no longer operating');

        $provider = $this->getProvider();
        $results = $provider->geocodeQuery(GeocodeQuery::create('::ffff:129.67.242.154'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);
        $this->assertEquals('GB', $results->first()->getCountry()->getCode());
    }

    public function testGeocodeWithRuLocale()
    {
        $this->markTestSkipped('Web service no longer operating');

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
        $this->markTestSkipped('Web service no longer operating');

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
        $this->markTestSkipped('Web service no longer operating');

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
        $provider = $this->getProvider();
        $provider->geocodeQuery(GeocodeQuery::create('87.227.124.53'));
    }

    private function getProvider(): FreeGeoIp
    {
        return new FreeGeoIp($this->getMockedHttpClient(), 'https://internal.geocoder/json/%s');
    }
}
