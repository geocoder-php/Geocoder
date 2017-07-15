<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\TomTom\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\TomTom\TomTom;

class TomTomTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName()
    {
        $provider = new TomTom($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('tomtom', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocodeWithAddress()
    {
        $provider = new TomTom($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('Tagensvej 47, 2200 København N'));
    }

    public function testGeocodeWithRealAddress()
    {
        $provider = new TomTom($this->getHttpClient($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Tagensvej 47, 2200 København N')->withLocale('en-GB'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(55.70, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(12.5529, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNull($result->getBounds());
        $this->assertEquals(47, $result->getStreetNumber());
        $this->assertEquals('Tagensvej', $result->getStreetName());
        $this->assertEquals(2200, $result->getPostalCode());
        $this->assertEquals('Copenhagen', $result->getLocality());
        $this->assertCount(0, $result->getAdminLevels());
        $this->assertEquals('Denmark', $result->getCountry()->getName());
        $this->assertEquals('DK', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealAddressWithFrenchLocale()
    {
        $provider = new TomTom($this->getHttpClient($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('Tagensvej 47, 2200 København N')->withLocale('fr-FR'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TomTom provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new TomTom($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TomTom provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new TomTom($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TomTom provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithIPv4()
    {
        $provider = new TomTom($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The TomTom provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithIPv6()
    {
        $provider = new TomTom($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage No Map API Key provided
     */
    public function testReverseWithoutApiKey()
    {
        $provider = new TomTom($this->getMockedHttpClient(), null);
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testReverse()
    {
        $provider = new TomTom($this->getMockedHttpClient(), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    public function testReverseError400()
    {
        $error400 = <<<'XML'
<errorResponse version="" description="" errorCode="400"/>
XML;

        $provider = new TomTom($this->getMockedHttpClient($error400), 'api_key');
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testReverseWithRealCoordinates()
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getHttpClient($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.86321648955345, 2.3887719959020615));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.86323, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(2.38877, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNull($result->getBounds());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('20e Arrondissement Paris', $result->getSubLocality());
        $this->assertCount(0, $result->getAdminLevels());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealCoordinates()
    {
        if (!isset($_SERVER['TOMTOM_MAP_KEY'])) {
            $this->markTestSkipped('You need to configure the TOMTOM_MAP_KEY value in phpunit.xml');
        }

        $provider = new TomTom($this->getHttpClient($_SERVER['TOMTOM_MAP_KEY']), $_SERVER['TOMTOM_MAP_KEY']);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(56.5231, 10.0659));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(56.52435, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(10.06744, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNull($result->getBounds());
        $this->assertEquals(16, $result->getStreetNumber());
        $this->assertEquals('Stabelsvej', $result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Spentrup', $result->getLocality());
        $this->assertEquals('Spentrup', $result->getSubLocality());
        $this->assertCount(0, $result->getAdminLevels());
        $this->assertEquals('Denmark', $result->getCountry()->getName());
        $this->assertEquals('DK', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }
}
