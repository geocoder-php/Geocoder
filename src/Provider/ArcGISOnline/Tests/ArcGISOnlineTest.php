<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\ArcGISOnlineTest\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\ArcGISOnline\ArcGISOnline;

class ArcGISOnlineTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName()
    {
        $provider = new ArcGISOnline($this->getMockedHttpClient());
        $this->assertEquals('arcgis_online', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The ArcGISOnline provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new ArcGISOnline($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The ArcGISOnline provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new ArcGISOnline($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealAddress()
    {
        $provider = new ArcGISOnline($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.863279997000461, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(2.3890199980004354, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('10 Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Île-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('FRA', $result->getCountry()->getCode());

        $this->assertNull($result->getBounds());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getAdminLevels()->get(2)->getCode());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getCountry()->getName());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithInvalidAddressWithSourceCountry()
    {
        $provider = new ArcGISOnline($this->getHttpClient(), 'Denmark', true);
        $result = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testReverseWithRealCoordinates()
    {
        $provider = new ArcGISOnline($this->getHttpClient(), null);
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.863279997000461, 2.3890199980004354));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.863279997000461, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(2.3890199980004354, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('3 Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('FRA', $result->getCountry()->getCode());

        $this->assertNull($result->getBounds());
        $this->assertNull($result->getSubLocality());
        $this->assertEmpty($result->getAdminLevels());
        $this->assertNull($result->getCountry()->getName());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithCity()
    {
        $provider = new ArcGISOnline($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('Hannover'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(52.370518568000477, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(9.7332166860004463, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Niedersachsen', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('DEU', $result->getCountry()->getCode());

        $this->assertNull($result->getBounds());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getCountry()->getName());
        $this->assertNull($result->getTimezone());

        /** @var Location $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(47.111386795000499, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(-101.4265391569997, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('North Dakota', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(39.391768472000479, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(-77.440257128999633, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Maryland', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('USA', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(3);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(53.174198173, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(8.5069383810005, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Niedersachsen', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('DEU', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(4);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(-26.281805980999593, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(-48.849389793999649, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertEquals('Rua Doutor João Colin', $result->getStreetName());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Sul', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('BRA', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The ArcGISOnline provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv4()
    {
        $provider = new ArcGISOnline($this->getHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('88.188.221.14'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The ArcGISOnline provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = new ArcGISOnline($this->getHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }
}
