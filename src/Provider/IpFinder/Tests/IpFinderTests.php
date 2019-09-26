<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IpFinder\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Provider\IpFinder\IpFinder;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class IpFinderTests extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName()
    {
        $provider = new IpFinder($this->getMockedHttpClient());
        $this->assertEquals('ipfinder', $provider->getName());
    }

    public function testGeocodeWithNoKey()
    {
        $provider = new IpFinder($this->getMockedHttpClient());
        $this->assertEquals('free', $provider::DEFAULT_API_TOKEN);
    }

    public function testGeocodeKey()
    {
        $provider = new IpFinder($this->getMockedHttpClient(), 'TOKEN');
        $this->assertEquals('TOKEN', $provider->apiKey);
    }

    public function testGeocodeWithAddress()
    {
        $provider = new IpFinder($this->getMockedHttpClient());
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The IpFinder provider support only IP addresses.');
        $provider->geocodeQuery(GeocodeQuery::create('Egypt, France'));
    }

    public function testGeocodeWith401Code()
    {
        $json = json_encode(['errors' => [0 => ['code' => 401]]]);
        $provider = new IpFinder($this->getMockedHttpClient($json), 'asdsad');
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('No API Key was specified, invalid API Key.');
        $result = $provider->geocodeQuery(GeocodeQuery::create('1.0.0.0'));
    }

    public function testGeocodeWith404Code()
    {
        $json = json_encode(['errors' => [0 => ['code' => 404]]]);
        $provider = new IpFinder($this->getMockedHttpClient($json), 'asdsad');
        $this->expectException(InvalidCredentials::class);
        $this->expectExceptionMessage('The requested resource does not exist.');
        $result = $provider->geocodeQuery(GeocodeQuery::create('1.0.0.0'));
    }

    public function testGeocodeWith104Code()
    {
        $json = json_encode(['errors' => [0 => ['code' => 104]]]);
        $provider = new IpFinder($this->getMockedHttpClient($json), 'asdsad');
        $this->expectException(QuotaExceeded::class);
        $this->expectExceptionMessage('You have reached your usage limit. Upgrade your plan if necessary.');
        $result = $provider->geocodeQuery(GeocodeQuery::create('1.0.0.0'));
    }

    public function testGeocodeWithIPv4()
    {
        $provider = new IpFinder($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('1.0.0.0'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('South Brisbane', $result->getLocality());
        $this->assertEquals('Australia', $result->getCountry()->getName());
        $this->assertEquals('AU', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithIPv6()
    {
        $provider = new IpFinder($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('2c0f:fb50:4003::'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Nairobi', $result->getLocality());
        $this->assertEquals('Kenya', $result->getCountry()->getName());
        $this->assertEquals('KE', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new IpFinder($this->getMockedHttpClient());
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
        $provider = new IpFinder($this->getMockedHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('::1'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    public function testReverse()
    {
        $provider = new IpFinder($this->getMockedHttpClient());
        $this->expectException(UnsupportedOperation::class);
        $this->expectExceptionMessage('The IpFinder provider is not able to do reverse geocoding.');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));
    }
}
