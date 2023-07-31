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
use Geocoder\Location;
use Geocoder\Provider\Ipstack\Ipstack;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

/**
 * @author Jonas Gielen <gielenjonas@gmail.com>
 */
class IpstackTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new Ipstack($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('ipstack', $provider->getName());
    }

    public function testGeocodeWithNoKey(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('No API key provided.');

        $provider = new Ipstack($this->getMockedHttpClient(), '');
    }

    public function testGeocodeWithAddress(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Ipstack provider does not support street addresses.');

        $provider = new Ipstack($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithLocalhostIPv4(): void
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

    public function testGeocodeWithLocalhostIPv6(): void
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

    public function testGeocodeWithRealIPv4(): void
    {
        $provider = new Ipstack($this->getHttpClient($_SERVER['IPSTACK_API_KEY']), $_SERVER['IPSTACK_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(37.751, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-97.822, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    public function testGeocodeWithRealIPv4InFrench(): void
    {
        $provider = new Ipstack($this->getHttpClient($_SERVER['IPSTACK_API_KEY']), $_SERVER['IPSTACK_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59')->withLocale('fr'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(37.751, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-97.822, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertEquals('États-Unis', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    public function testReverse(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Ipstack provider is not able to do reverse geocoding.');

        $provider = new Ipstack($this->getMockedHttpClient(), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    public function testGeocodeWith301Code(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidArgument::class);
        $this->expectExceptionMessage('Invalid request (a required parameter is missing).');

        $json = <<<'JSON'
{"success":false,"error":{"code":301}}
JSON;
        $provider = new Ipstack($this->getMockedHttpClient($json), 'api_key');
        $result = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWith303Code(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidArgument::class);
        $this->expectExceptionMessage('Bulk requests are not supported on your plan. Please upgrade your subscription.');

        $json = <<<'JSON'
{"success":false,"error":{"code":303,"type":"batch_not_supported_on_plan","info":"Bulk requests are not supported on your plan. Please upgrade your subscription."}}
JSON;
        $provider = new Ipstack($this->getMockedHttpClient($json), 'api_key');
        $result = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWith104Code(): void
    {
        $this->expectException(\Geocoder\Exception\QuotaExceeded::class);
        $this->expectExceptionMessage('The maximum allowed amount of monthly API requests has been reached.');

        $json = <<<'JSON'
{"success":false,"error":{"code":104}}
JSON;
        $provider = new Ipstack($this->getMockedHttpClient($json), 'api_key');
        $result = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWith101Code(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('No API Key was specified or an invalid API Key was specified.');

        $json = <<<'JSON'
{"success":false,"error":{"code":101,"type":"invalid_access_key","info":"You have not supplied a valid API Access Key. [Technical Support: support@apilayer.com]"}}
JSON;
        $provider = new Ipstack($this->getMockedHttpClient($json), 'api_key');
        $result = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }
}
