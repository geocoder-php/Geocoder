<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GeocodioProvider;

class GeocodioProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new GeocodioProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('geocodio', $provider->getName());
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not find results for given query: http://api.geocod.io/v1/geocode?q=foobar&api_key=9999
     */
    public function testGetGeocodedData()
    {
        $provider = new GeocodioProvider($this->getMockAdapter(), '9999');
        $provider->getGeocodedData('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query: http://api.geocod.io/v1/geocode?q=1+Infinite+Loop+Cupertino%2C+CA+95014&api_key=9999
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new GeocodioProvider($this->getMockAdapterReturns(null), '9999');
        $provider->getGeocodedData('1 Infinite Loop Cupertino, CA 95014');
    }

	/**
	 * @expectedException \Geocoder\Exception\InvalidCredentialsException
	 * @expectedExceptionMessage Invalid API Key
	 */
	public function testGetGeocodedDataWithBadAPIKeyThrowsException()
	{
        $provider = new GeocodioProvider($this->getAdapter(), '9999');
        $results  = $provider->getGeocodedData('1 Infinite Loop Cupertino, CA 95014');
	}
	
    public function testGetGeocodedDataWithRealAddress()
    {
        $api_key = $_SERVER['GEOCODIO_API_KEY'];

        if (empty($api_key)) {
            $this->markTestSkipped('You need to configure the GEOCODIO_API_KEY value in phpunit.xml');
        }

        $provider = new GeocodioProvider($this->getAdapter(), $api_key);
        $results  = $provider->getGeocodedData('1 Infinite Loop Cupertino, CA 95014');
        
        $this->assertInternalType('array', $results);

        $result = $results[0];
        $this->assertEquals(37.331551291667, $result['latitude'], '', 0.01);
        $this->assertEquals(-122.03057125, $result['longitude'], '', 0.01);
        $this->assertNull($result['bounds']);
        $this->assertEquals('1', $result['streetNumber']);
        $this->assertEquals('Infinite Loop', $result['streetName']);
        $this->assertEquals(95014, $result['zipcode']);
        $this->assertEquals('Cupertino', $result['city']);
        $this->assertEquals('Santa Clara County', $result['county']);
        $this->assertEquals('CA', $result['region']);
        $this->assertEquals('US', $result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     */
    public function testGetReversedData()
    {
        if (!isset($_SERVER['GEOCODIO_API_KEY'])) {
            $this->markTestSkipped('You need to configure the GEOCODIO_API_KEY value in phpunit.xml');
        }

        $provider = new GeocodioProvider($this->getMockAdapter(), $_SERVER['GEOCODIO_API_KEY']);
        $provider->getReversedData(array(1, 2));
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['GEOCODIO_API_KEY'])) {
            $this->markTestSkipped('You need to configure the GEOCODIO_API_KEY value in phpunit.xml');
        }

        $provider = new GeocodioProvider($this->getAdapter(), $_SERVER['GEOCODIO_API_KEY']);
        $result   = $provider->getReversedData(array(37.331551291667, -122.03057125));

        $this->assertInternalType('array', $result);

        $result = $result[0];
        $this->assertEquals(37.331551291667, $result['latitude'], '', 0.01);
        $this->assertEquals(-122.03057125, $result['longitude'], '', 0.01);
        $this->assertNull($result['bounds']);
        $this->assertEquals('Infinite Loop', $result['streetName']);
        $this->assertEquals(95014, $result['zipcode']);
        $this->assertEquals('Cupertino', $result['city']);
        $this->assertEquals('Santa Clara County', $result['county']);
        $this->assertEquals('CA', $result['region']);
        $this->assertEquals('US', $result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }
}