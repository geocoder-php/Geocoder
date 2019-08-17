<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Pelias\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Provider\Pelias\Pelias;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class PeliasTest extends BaseTestCase
{
    protected function getCacheDir()
    {
        return __DIR__ . '/.cached_responses';
    }

    public function testGetName()
    {
        $provider = new Pelias($this->getMockedHttpClient(), 'http://localhost/');
        $this->assertEquals('pelias', $provider->getName());
    }

    public function testGeocode()
    {
        $provider = new Pelias($this->getMockedHttpClient('{}'), 'http://localhost/');
        $result = $provider->geocodeQuery(GeocodeQuery::create('foobar'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testReverse()
    {
        $provider = new Pelias($this->getMockedHttpClient('{}'), 'http://localhost/');
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(0, 0));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceeded
     * @expectedExceptionMessage Valid request but quota exceeded.
     */
    public function testGeocodeQuotaExceeded()
    {
        $provider = new Pelias(
            $this->getMockedHttpClient(
                '{
                    "meta": {
                        "version": 1,
                        "status_code": 429
                    },
                    "results": {
                        "error": {
                            "type": "QpsExceededError",
                            "message": "Queries per second exceeded: Queries exceeded (6 allowed)."
                        }
                    }
                }'
            ),
            'http://localhost/'
        );
        $provider->geocodeQuery(GeocodeQuery::create('New York'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage Invalid or missing api key.
     */
    public function testGeocodeInvalidApiKey()
    {
        $provider = new Pelias(
            $this->getMockedHttpClient(
                '{
                    "meta": {
                        "version": 1,
                        "status_code": 403
                    },
                    "results": {
                        "error": {
                            "type": "KeyError",
                            "message": "No api_key specified."
                        }
                    }
                }'
            ),
            'http://localhost/'
        );
        $provider->geocodeQuery(GeocodeQuery::create('New York'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Pelias provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new Pelias($this->getMockedHttpClient(), 'http://localhost/');
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Pelias provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new Pelias($this->getMockedHttpClient(), 'http://localhost/');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Pelias provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv4()
    {
        $provider = new Pelias($this->getMockedHttpClient(), 'http://localhost/');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Pelias provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIPv6()
    {
        $provider = new Pelias($this->getMockedHttpClient(), 'http://localhost/');
        $provider->geocodeQuery(GeocodeQuery::create('::ffff:74.200.247.59'));
    }
}
