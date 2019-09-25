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

/**
 * @author Jonas Gielen <gielenjonas@gmail.com>
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

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The IpFinder provider support only IP addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new IpFinder($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('Egypt, France'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgument
     * @expectedExceptionMessage No API Key was specified, invalid API Key.
     */
    public function testGeocodeWith401Code()
    {
        $json = <<<'JSON'
{
   "errors": [
      {
         "code": 401
      }
   ]
}
JSON;
        $provider = new IpFinder($this->getMockedHttpClient($json), 'asdsad');
        $result = $provider->geocodeQuery(GeocodeQuery::create('1.0.0.0'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage The requested resource does not exist.
     */
    public function testGeocodeWith404Code()
    {
        $json = <<<'JSON'
{
   "errors": [
      {
         "code": 404
      }
   ]
}
JSON;
        $provider = new IpFinder($this->getMockedHttpClient($json), 'asdsad');
        $result = $provider->geocodeQuery(GeocodeQuery::create('1.0.0.0'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage Method Not Allowed.
     */
    public function testGeocodeWith405Code()
    {
        $json = <<<'JSON'
{
   "errors": [
      {
         "code": 405
      }
   ]
}
JSON;
        $provider = new IpFinder($this->getMockedHttpClient($json), 'asdsad');
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
}
