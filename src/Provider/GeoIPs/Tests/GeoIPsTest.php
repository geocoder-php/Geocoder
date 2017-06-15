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
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\GeoIPs\GeoIPs;

class GeoIPsTest extends BaseTestCase
{
    public function testGetName()
    {
        $provider = new GeoIPs($this->getMockedHttpClient(), 'api_key');
        $this->assertEquals('geo_ips', $provider->getName());
    }

    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGeocodeWithNullApiKey()
    {
        $provider = new GeoIPs($this->getMockedHttpClient(), null);
        $provider->geocodeQuery(GeocodeQuery::create('foo'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoIPs provider does not support street addresses, only IPv4 addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new GeoIPs($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithLocalhostIPv4()
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

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoIPs provider does not support IPv6 addresses, only IPv4 addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new GeoIPs($this->getMockedHttpClient(), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocodeWithRealIPv4GetsNullContent()
    {
        $provider = new GeoIPs($this->getMockedHttpClient(null), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocodeWithRealIPv4GetsEmptyContent()
    {
        $provider = new GeoIPs($this->getMockedHttpClient(''), 'api_key');
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    public function testGeocodeWithRealIPv4GetsFakeContentFormattedEmpty()
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
        $this->assertNull($result->getCountry()->getName());
        $this->assertNull($result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealIPv4GetsFakeContent()
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
        $this->assertEquals(40.3402, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(-111.6073, $result->getCoordinates()->getLongitude(), '', 0.0001);
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

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage The API key associated with your request was not recognized.
     */
    public function testGeocodeWithRealIPv4AndInvalidApiKeyGetsFakeContent()
    {
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

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage The API key has not been approved or has been disabled.
     */
    public function testGeocodeWithRealIPv4AndInvalidApiKeyGetsFakeContent2()
    {
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

    /**
     * @expectedException \Geocoder\Exception\QuotaExceeded
     * @expectedExceptionMessage The service you have requested is over capacity.
     */
    public function testGeocodeWithRealIPv4AndQuotaExceeded()
    {
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

    /**
     * @expectedException \Geocoder\Exception\InvalidArgument
     * @expectedExceptionMessage The API call should include a valid IP address.
     */
    public function testGeocodeGetsFakeContentWithIpNotFound()
    {
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

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage The API call should include a API key parameter.
     */
    public function testGeocodeGetsFakeContentWithKeyNotFound()
    {
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

    public function testGeocodeWithRealIPv4()
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
        $this->assertEquals(40.3402, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(-111.6073, $result->getCoordinates()->getLongitude(), '', 0.0001);
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

    public function testGeocodeWithRealIPv4ZeroResults()
    {
        if (!isset($_SERVER['GEOIPS_API_KEY'])) {
            $this->markTestSkipped('You need to configure the GEOIPS_API_KEY value in phpunit.xml');
        }

        $provider = new GeoIPs($this->getHttpClient($_SERVER['GEOIPS_API_KEY']), $_SERVER['GEOIPS_API_KEY']);
        $result = $provider->geocodeQuery(GeocodeQuery::create('255.255.150.96'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GeoIPs provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new GeoIPs($this->getMockedHttpClient(), 'api_key');
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }
}
