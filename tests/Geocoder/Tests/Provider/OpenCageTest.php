<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\OpenCage;

/**
 * @author mtm <mtm@opencagedata.com>
 */
class OpenCageTest extends TestCase
{
    public function testGetName()
    {
        $provider = new OpenCage($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('opencage', $provider->getName());
    }

    /**
     * @expectedException Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not find results for query "http://api.opencagedata.com/geocode/v1/json?key=api_key&query=foobar&limit=5&pretty=1".
     */
    public function testGetGeocodedData()
    {
        $provider = new OpenCage($this->getMockAdapterReturns('{}'), 'api_key');
        $provider->geocode('foobar');
    }

    /**
     * @expectedException Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not find results for query "https://api.opencagedata.com/geocode/v1/json?key=api_key&query=foobar&limit=5&pretty=1".
     */
    public function testSslSchema()
    {
        $provider = new OpenCage($this->getMockAdapterReturns('{}'), 'api_key', true);
        $provider->geocode('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://api.opencagedata.com/geocode/v1/json?key=api_key&query=10+avenue+Gambetta%2C+Paris%2C+France&limit=5&pretty=1".
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new OpenCage($this->getMockAdapterReturns(null), 'api_key');
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getAdapter(), $_SERVER['OPENCAGE_API_KEY']);
        $results  = $provider->geocode('10 avenue Gambetta, Paris, France');

        $this->assertInternalType('array', $results);
        $this->assertCount(3, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(        48.866205, $result['latitude'], '', 0.01);
        $this->assertEquals(         2.389089, $result['longitude'], '', 0.01);
        $this->assertEquals(               10, $result['streetNumber']);
        $this->assertEquals('Avenue Gambetta', $result['streetName']);
        $this->assertEquals(            75020, $result['postalCode']);
        $this->assertEquals(          'Paris', $result['locality']);
        $this->assertEquals(          'Paris', $result['county']);
        $this->assertEquals(  'Ile-de-France', $result['region']);
        $this->assertEquals(         'France', $result['country']);
        $this->assertEquals(             'FR', $result['countryCode']);
        $this->assertEquals(   'Europe/Paris', $result['timezone']);
        $this->assertEquals( array('south' => 48.863142699999997,
                                   'west'  => 2.3890394000000001,
                                   'north' => 48.863242700000001,
                                   'east'  => 2.3891393999999999), $result['bounds']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     */
    public function testGetReversedData()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getMockAdapter(), $_SERVER['OPENCAGE_API_KEY']);
        $provider->reverse(array(1, 2));
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getAdapter(), $_SERVER['OPENCAGE_API_KEY']);
        $result   = $provider->reverse(54.0484068, -2.7990345);

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(      54.0484068, $result['latitude'], '', 0.001);
        $this->assertEquals(      -2.7990345, $result['longitude'], '', 0.001);
        $this->assertNull(                    $result['streetNumber']);
        $this->assertNull(                    $result['streetName']);
        $this->assertNull(                    $result['postalCode']);
        $this->assertEquals(     'Lancaster', $result['locality']);
        $this->assertEquals(    'Lancashire', $result['county']);
        $this->assertEquals(       'England', $result['region']);
        $this->assertEquals('United Kingdom', $result['country']);
        $this->assertEquals(            'GB', $result['countryCode']);
        $this->assertEquals('Europe/London' , $result['timezone']);
        $this->assertEquals( array('south' => 54.048273100000003,
                                   'west'  => -2.7998815000000001,
                                   'north' => 54.0494992,
                                   'east'  => -2.79813), $result['bounds']);
    }

    public function testGetGeocodedDataWithCity()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getAdapter(), $_SERVER['OPENCAGE_API_KEY']);
        $results  = $provider->geocode('Hanover');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(                 52.374478, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(                  9.738553, $results[0]['longitude'], '', 0.01);
        $this->assertEquals(                 'Hanover', $results[0]['locality']);
        $this->assertEquals(         'Region Hannover', $results[0]['county']);
        $this->assertEquals(            'Lower Saxony', $results[0]['region']);
        $this->assertEquals(                 'Germany', $results[0]['country']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(                 37.744783, $results[1]['latitude'], '', 0.01);
        $this->assertEquals(               -77.4464165, $results[1]['longitude'], '', 0.01);
        $this->assertNull(                              $results[1]['locality']);
        $this->assertEquals(                 'Hanover', $results[1]['county']);
        $this->assertEquals('United States of America', $results[1]['country']);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(                18.3840489, $results[2]['latitude'], '', 0.01);
        $this->assertEquals(                -78.131485, $results[2]['longitude'], '', 0.01);
        $this->assertNull(                              $results[2]['locality']);
        $this->assertEquals(                 'Hanover', $results[2]['county']);
        $this->assertEquals(                 'Jamaica', $results[2]['country']);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(                43.7033073, $results[3]['latitude'], '', 0.01);
        $this->assertEquals(               -72.2885663, $results[3]['longitude'], '', 0.01);
        $this->assertEquals(                 'Hanover', $results[3]['locality']);
        $this->assertEquals(          'Grafton County', $results[3]['county']);
        $this->assertEquals(           'New Hampshire', $results[3]['region']);
        $this->assertEquals('United States of America', $results[3]['country']);
    }

    public function testGetGeocodedDataWithCityDistrict()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getAdapter(), $_SERVER['OPENCAGE_API_KEY']);
        $result   = $provider->geocode('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(              50.189062, $result['latitude'], '', 0.01);
        $this->assertEquals(               8.636567, $result['longitude'], '', 0.01);
        $this->assertEquals(                     10,$result['streetNumber']);
        $this->assertEquals('Kalbacher Hauptstraße', $result['streetName']);
        $this->assertEquals(                  60437, $result['postalCode']);
        $this->assertEquals(            'Frankfurt', $result['locality']);
        $this->assertEquals(            'Frankfurt', $result['county']);
        $this->assertEquals(                'Hesse', $result['region']);
        $this->assertEquals(              'Germany', $result['country']);
        $this->assertEquals(                   'DE', $result['countryCode']);
        $this->assertEquals(        'Europe/Berlin', $result['timezone']);
        $this->assertNull($result['regionCode']);
    }

    public function testGetGeocodedDataWithLocale()
    {
        if (!isset($_SERVER['OPENCAGE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the OPENCAGE_API_KEY value in phpunit.xml');
        }

        $provider = new OpenCage($this->getAdapter(), $_SERVER['OPENCAGE_API_KEY'], true, 'es');
        $result   = $provider->geocode('London');

        $this->assertInternalType('array', $result);
        $this->assertCount(5, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(            'Londres', $result['locality']);
        $this->assertEquals(            'Londres', $result['county']);
        $this->assertEquals(         'Inglaterra', $result['region']);
        $this->assertEquals(        'Reino Unido', $result['country']);
        $this->assertEquals(                 'GB', $result['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OpenCage provider does not support IP addresses, only street addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new OpenCage($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OpenCage provider does not support IP addresses, only street addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new OpenCage($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OpenCage provider does not support IP addresses, only street addresses.
     */
    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new OpenCage($this->getAdapter(), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The OpenCage provider does not support IP addresses, only street addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new OpenCage($this->getAdapter(), 'api_key');
        $provider->geocode('::ffff:74.200.247.59');
    }
}

class OpenCageMock extends OpenCage
{
    /**
     * Short circuits so assertions can inspect the
     * executed query URL
     */
    protected function executeQuery($query)
    {
        return $query;
    }
}
