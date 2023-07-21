<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GeoIPs\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Provider\GeoIPs\GeoIPs;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

class GeoIPsTest extends BaseTestCase
{
    public function testGetName(): void
    {
        $provider = new GeoIPs($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('geo_ips', $provider->getName());
    }

    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGeocodeWithAddress(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The GeoIPs provider does not support street addresses, only IPv4 addresses.');

        $provider = new GeoIPs($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $provider = new GeoIPs($this->getMockedHttpClient(), 'api_key');
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
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The GeoIPs provider does not support IPv6 addresses, only IPv4 addresses.');

        $provider = new GeoIPs($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithRealIPv4GetsNullContent(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

        $provider = new GeoIPs($this->getMockedHttpClient(null), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithRealIPv4GetsEmptyContent(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidServerResponse::class);

        $provider = new GeoIPs($this->getMockedHttpClient(''), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithRealIPv4GetsFakeContentFormattedEmpty(): void
    {
        $json = '{"response":{
            "status": "Propper Request",
            "message": "Success",
            "notes": "The following results has been returned",
            "code": "200_1",
            "location": {
                "ip" : "66.147.244.214",
                "owner" : "",
                "continent_name" : "",
                "continent_code" : "",
                "country_name" : "",
                "country_code" : "",
                "region_name" : "",
                "region_code" : "",
                "county_name" : "",
                "city_name" : "",
                "latitude" : "",
                "longitude" : "",
                "timezone" : ""
            },
            "unit_test": {
                "elapsed_time": "0.0676",
                "memory_usage": "2.2MB"
            }
        }}';

        $provider = new GeoIPs($this->getMockedHttpClient($json), 'api_key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('66.147.244.214'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNull($result->getCoordinates());

        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getLocality());
        $this->assertEmpty($result->getAdminLevels());
        $this->assertNull($result->getCountry());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealIPv4GetsFakeContent(): void
    {
        $json = '{"response":{
            "status": "Propper Request",
            "message": "Success",
            "notes": "The following results has been returned",
            "code": "200_1",
            "location": {
                "ip" : "66.147.244.214",
                "owner" : "BLUEHOST INC.",
                "continent_name" : "NORTH AMERICA",
                "continent_code" : "NA",
                "country_name" : "UNITED STATES",
                "country_code" : "US",
                "region_name" : "UTAH",
                "region_code" : "UT",
                "county_name" : "UTAH",
                "city_name" : "PROVO",
                "latitude" : "40.3402",
                "longitude" : "-111.6073",
                "timezone" : "MST"
            }
        }}';

        $provider = new GeoIPs($this->getMockedHttpClient($json), 'api_key');
        $results = $provider->geocodeQuery(GeocodeQuery::create('66.147.244.214'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(40.3402, $result->getCoordinates()->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(-111.6073, $result->getCoordinates()->getLongitude(), 0.0001);
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('PROVO', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('UTAH', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('UTAH', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('UT', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('UNITED STATES', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('MST', $result->getTimezone());
    }

    public function testGeocodeWithRealIPv4AndInvalidApiKeyGetsFakeContent(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('The API key associated with your request was not recognized.');

        $provider = new GeoIPs(
            $this->getMockedHttpClient(
                '{
                    "error": {
                        "status": "Forbidden",
                        "message": "Not Authorized",
                        "notes": "The API key associated with your request was not recognized",
                        "code": "403_1",
                        "unit_test": {
                            "elapsed_time": "0.0474",
                            "memory_usage": "2.2MB"
                        }
                    }
                }'
            ),
            'api_key'
        );
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithRealIPv4AndInvalidApiKeyGetsFakeContent2(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('The API key has not been approved or has been disabled.');

        $provider = new GeoIPs(
            $this->getMockedHttpClient(
                '{
                    "error": {
                        "status": "Forbidden",
                        "message": "Account Inactive",
                        "notes": "The API key has not been approved or has been disabled.",
                        "code": "403_2",
                        "unit_test": {
                            "elapsed_time": "0.0474",
                            "memory_usage": "2.2MB"
                        }
                    }
                }'
            ),
            'api_key'
        );
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithRealIPv4AndQuotaExceeded(): void
    {
        $this->expectException(\Geocoder\Exception\QuotaExceeded::class);
        $this->expectExceptionMessage('The service you have requested is over capacity.');

        $provider = new GeoIPs(
            $this->getMockedHttpClient(
                '{
                    "error": {
                        "status": "Forbidden",
                        "message": "Limit Exceeded",
                        "notes": "The service you have requested is over capacity.",
                        "code": "403_3",
                        "unit_test": {
                            "elapsed_time": "0.0474",
                            "memory_usage": "2.2MB"
                        }
                    }
                }'
            ),
            'api_key'
        );
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeGetsFakeContentWithIpNotFound(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidArgument::class);
        $this->expectExceptionMessage('The API call should include a valid IP address.');

        $provider = new GeoIPs(
            $this->getMockedHttpClient(
                '{
                    "error": {
                        "status": "Bad Request",
                        "message": "Error in the URI",
                        "notes": "The API call should include a valid IP address.",
                        "code": "400_2",
                        "unit_test": {
                            "elapsed_time": "0.0474",
                            "memory_usage": "2.2MB"
                        }
                    }
                }'
            ),
            'api_key'
        );
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeGetsFakeContentWithKeyNotFound(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidCredentials::class);
        $this->expectExceptionMessage('The API call should include a API key parameter.');

        $provider = new GeoIPs(
            $this->getMockedHttpClient(
                '{
                    "error": {
                        "status": "Bad Request",
                        "message": "Error in the URI",
                        "notes": "The API call should include a API key parameter.",
                        "code": "400_1",
                        "unit_test": {
                            "elapsed_time": "0.0474",
                            "memory_usage": "2.2MB"
                        }
                    }
                }'
            ),
            'api_key'
        );
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithRealIPv4(): void
    {
        if (!isset($_SERVER['GEOIPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the GEOIPS_API_KEY value in phpunit.xml');
        }

        $provider = new GeoIPs($this->getHttpClient($_SERVER['GEOIPS_API_KEY']), $_SERVER['GEOIPS_API_KEY']);
        $results = $provider->geocodeQuery(GeocodeQuery::create('66.147.244.214'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEqualsWithDelta(40.3402, $result->getCoordinates()->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(-111.6073, $result->getCoordinates()->getLongitude(), 0.0001);
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('PROVO', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('UTAH', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('UTAH', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('UT', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('UNITED STATES', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('MST', $result->getTimezone());
    }

    public function testGeocodeWithRealIPv4ZeroResults(): void
    {
        if (!isset($_SERVER['GEOIPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the GEOIPS_API_KEY value in phpunit.xml');
        }

        $provider = new GeoIPs($this->getHttpClient($_SERVER['GEOIPS_API_KEY']), $_SERVER['GEOIPS_API_KEY']);
        $result = $provider->geocodeQuery(GeocodeQuery::create('255.255.150.96'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testReverse(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The GeoIPs provider is not able to do reverse geocoding.');

        $provider = new GeoIPs($this->getMockedHttpClient(), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }
}
