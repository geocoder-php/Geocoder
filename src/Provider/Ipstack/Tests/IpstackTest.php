<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Ipstack\Tests;

use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Provider\Ipstack\Ipstack;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

/**
 * @author Jonas Gielen <gielenjonas@gmail.com>
 */
class IpstackTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName()
    {
        $provider = new Ipstack($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('ipstack', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage No API key provided.
     */
    public function testGeocodeWithNoKey()
    {
        $provider = new Ipstack($this->getMockedHttpClient(), '');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Ipstack provider does not support street addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new Ipstack($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new Ipstack($this->getMockedHttpClient(), 'api_key');
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
        $provider = new Ipstack($this->getMockedHttpClient(), 'api_key');
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
        $provider = new Ipstack($this->getHttpClient($_SERVER['IPSTACK_API_KEY']), $_SERVER['IPSTACK_API_KEY']);
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

    public function testGeocodeWithRealIPv4InFrench()
    {
        $provider = new Ipstack($this->getHttpClient($_SERVER['IPSTACK_API_KEY']), $_SERVER['IPSTACK_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59')->withLocale('fr'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(37.751, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-97.822, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertEquals('États-Unis', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Ipstack provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new Ipstack($this->getMockedHttpClient(), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgument
     * @expectedExceptionMessage Invalid request (a required parameter is missing).
     */
    public function testGeocodeWith301Code()
    {
        $json = <<<'JSON'
{"success":false,"error":{"code":301}}
JSON;
        $provider = new Ipstack($this->getMockedHttpClient($json), 'api_key');
        $result = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgument
     * @expectedExceptionMessage Bulk requests are not supported on your plan. Please upgrade your subscription.
     */
    public function testGeocodeWith303Code()
    {
        $json = <<<'JSON'
{"success":false,"error":{"code":303,"type":"batch_not_supported_on_plan","info":"Bulk requests are not supported on your plan. Please upgrade your subscription."}}
JSON;
        $provider = new Ipstack($this->getMockedHttpClient($json), 'api_key');
        $result = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceeded
     * @expectedExceptionMessage The maximum allowed amount of monthly API requests has been reached.
     */
    public function testGeocodeWith104Code()
    {
        $json = <<<'JSON'
{"success":false,"error":{"code":104}}
JSON;
        $provider = new Ipstack($this->getMockedHttpClient($json), 'api_key');
        $result = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage No API Key was specified or an invalid API Key was specified.
     */
    public function testGeocodeWith101Code()
    {
        $json = <<<'JSON'
{"success":false,"error":{"code":101,"type":"invalid_access_key","info":"You have not supplied a valid API Access Key. [Technical Support: support@apilayer.com]"}}
JSON;
        $provider = new Ipstack($this->getMockedHttpClient($json), 'api_key');
        $result = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }
}
