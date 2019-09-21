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
        $provider = new IpFinder($this->getMockedHttpClient(),'TOKEN');
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
}
