<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\MapTiler\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Model\Bounds;
use Geocoder\Provider\MapTiler\MapTiler;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

/**
 * @author Jonathan Beliën
 */
class MapTilerTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        if (!isset($_SERVER['MAPTILER_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPTILER_KEY value in phpunit.xml');
        }

        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The MapTiler provider does not support IP addresses.');

        $provider = new MapTiler($this->getMockedHttpClient(), $_SERVER['MAPTILER_KEY']);
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6()
    {
        if (!isset($_SERVER['MAPTILER_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPTILER_KEY value in phpunit.xml');
        }

        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The MapTiler provider does not support IP addresses.');

        $provider = new MapTiler($this->getMockedHttpClient(), $_SERVER['MAPTILER_KEY']);
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIPv6()
    {
        if (!isset($_SERVER['MAPTILER_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPTILER_KEY value in phpunit.xml');
        }

        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The MapTiler provider does not support IP addresses.');

        $provider = new MapTiler($this->getMockedHttpClient(), $_SERVER['MAPTILER_KEY']);
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:88.188.221.14'));
    }

    public function testGeocodeQueryStreet()
    {
        if (!isset($_SERVER['MAPTILER_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPTILER_KEY value in phpunit.xml');
        }

        $query = GeocodeQuery::create('Avenue Gambetta, Paris, France');
        $query = $query->withBounds(new Bounds(2.293039, 48.821036, 2.406336, 48.894899));

        $provider = new MapTiler($this->getHttpClient($_SERVER['MAPTILER_KEY']), $_SERVER['MAPTILER_KEY']);
        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        // $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.8658863, $result->getCoordinates()->getLatitude(), 0.00001);
        $this->assertEqualsWithDelta(2.3993232, $result->getCoordinates()->getLongitude(), 0.00001);
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('France', $result->getCountry());
    }

    public function testGeocodeQueryCity()
    {
        if (!isset($_SERVER['MAPTILER_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPTILER_KEY value in phpunit.xml');
        }

        $query = GeocodeQuery::create('Paris, France');

        $provider = new MapTiler($this->getHttpClient($_SERVER['MAPTILER_KEY']), $_SERVER['MAPTILER_KEY']);
        $results = $provider->geocodeQuery($query);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        // $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(48.85881, $result->getCoordinates()->getLatitude(), 0.00001);
        $this->assertEqualsWithDelta(2.320031, $result->getCoordinates()->getLongitude(), 0.00001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('France', $result->getCountry());
    }

    public function testReverseQuery()
    {
        if (!isset($_SERVER['MAPTILER_KEY'])) {
            $this->markTestSkipped('You need to configure the MAPTILER_KEY value in phpunit.xml');
        }

        $query = ReverseQuery::fromCoordinates(47.3774434, 8.528509);

        $provider = new MapTiler($this->getHttpClient($_SERVER['MAPTILER_KEY']), $_SERVER['MAPTILER_KEY']);
        $results = $provider->reverseQuery($query);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        // $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(47.3774434, $result->getCoordinates()->getLatitude(), 0.00001);
        $this->assertEqualsWithDelta(8.528509, $result->getCoordinates()->getLongitude(), 0.00001);
        $this->assertEquals('Zurich', $result->getLocality());
        $this->assertEquals('Switzerland', $result->getCountry());
    }
}
