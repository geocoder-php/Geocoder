<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\CloudMadeProvider;

class CloudMadeProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new CloudMadeProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('cloudmade', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new CloudMadeProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocoding.cloudmade.com/api_key/geocoding/v2/find.js?query=foobar&distance=closest&return_location=true&results=5
     */
    public function testGetGeocodedData()
    {
        $provider = new CloudMadeProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocoding.cloudmade.com/api_key/geocoding/v2/find.js?query=&distance=closest&return_location=true&results=5
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new CloudMadeProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocoding.cloudmade.com/api_key/geocoding/v2/find.js?query=&distance=closest&return_location=true&results=5
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new CloudMadeProvider($this->getMockAdapter(), 'api_key');
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The CloudMadeProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new CloudMadeProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The CloudMadeProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new CloudMadeProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocoding.cloudmade.com/api_key/geocoding/v2/find.js?query=36+Quai+des+Orf%C3%A8vres%2C+Paris%2C+France&distance=closest&return_location=true&results=5
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new CloudMadeProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getGeocodedData('36 Quai des Orfèvres, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage Invalid API Key invalid_key
     */
    public function testGetGeocodedDataWithInvalidApiKey()
    {
        $provider = new CloudMadeProvider($this->getMockAdapterReturns('Forbidden request'), 'invalid_key');
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage Invalid API Key invalid_key
     */
    public function testGetGeocodedDataWithRealInvalidApiKey()
    {
        $provider = new CloudMadeProvider($this->getAdapter(), 'invalid_key');
        $provider->getGeocodedData('foo');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new CloudMadeProvider($this->getAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $result   = $provider->getGeocodedData('36 Quai des Orfèvres, Paris, France');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(48.85645, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.35243, $result['longitude'], '', 0.0001);
        $this->assertArrayHasKey('south', $result['bounds']);
        $this->assertArrayHasKey('west', $result['bounds']);
        $this->assertArrayHasKey('north', $result['bounds']);
        $this->assertArrayHasKey('east', $result['bounds']);
        $this->assertEquals(48.70804, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(2.12785, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(49.00442, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(2.57701, $result['bounds']['east'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Paris', $result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertEquals('Ile-del-france', $result['region']);
        $this->assertEquals('Ile-del-france', $result['county']);
        $this->assertEquals('France', $result['country']);

        // not provided
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocoding.cloudmade.com/api_key/geocoding/v2/find.js?around=1.000000,2.000000&object_type=address&return_location=true&results=5
     */
    public function testGetReversedData()
    {
        $provider = new CloudMadeProvider($this->getMockAdapter(), 'api_key');
        $provider->getReversedData(array(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocoding.cloudmade.com/api_key/geocoding/v2/find.js?around=48.856570,2.353250&object_type=address&return_location=true&results=5
     */
    public function testGetReversedDataWithCoordinatesGetsNullContent()
    {
        $provider = new CloudMadeProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getReversedData(array(48.85657, 2.35325));
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new CloudMadeProvider($this->getAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $results  = $provider->getReversedData(array(48.85657, 2.35325));

        $this->assertInternalType('array', $results);
        $this->assertCount(4, $results); // 4 results are returned by the provider

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(48.85657, $results[0]['latitude'], '', 0.0001);
        $this->assertEquals(2.35325, $results[0]['longitude'], '', 0.0001);
        $this->assertArrayHasKey('south', $results[0]['bounds']);
        $this->assertArrayHasKey('west', $results[0]['bounds']);
        $this->assertArrayHasKey('north', $results[0]['bounds']);
        $this->assertArrayHasKey('east', $results[0]['bounds']);
        $this->assertEquals(48.85657, $results[0]['bounds']['south'], '', 0.0001);
        $this->assertEquals(2.35325, $results[0]['bounds']['west'], '', 0.0001);
        $this->assertEquals(48.85657, $results[0]['bounds']['north'], '', 0.0001);
        $this->assertEquals(2.35325, $results[0]['bounds']['east'], '', 0.0001);
        $this->assertEquals(5, $results[0]['streetNumber']);
        $this->assertEquals('Rue Lobau', $results[0]['streetName']);
        $this->assertNull($results[0]['zipcode']);
        $this->assertEquals('Paris', $results[0]['city']);
        $this->assertEquals('Ile-del-france', $results[0]['region']);
        $this->assertEquals('Ile-del-france', $results[0]['county']);
        $this->assertEquals('France', $results[0]['country']);

        // not provided
        $this->assertNull($results[0]['countryCode']);
        $this->assertNull($results[0]['timezone']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(48.85658, $results[1]['latitude'], '', 0.0001);
        $this->assertEquals(2.35381, $results[1]['longitude'], '', 0.0001);
        $this->assertEquals('Rue Lobau', $results[1]['streetName']);
        $this->assertEquals('Paris', $results[1]['city']);
        $this->assertEquals('France', $results[1]['country']);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(48.85714, $results[2]['latitude'], '', 0.0001);
        $this->assertEquals(2.35348, $results[2]['longitude'], '', 0.0001);
        $this->assertEquals('Rue de Rivoli', $results[2]['streetName']);
        $this->assertEquals('Paris', $results[2]['city']);
        $this->assertEquals('France', $results[2]['country']);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(48.8571, $results[3]['latitude'], '', 0.0001);
        $this->assertEquals(2.35362, $results[3]['longitude'], '', 0.0001);
        $this->assertEquals('Rue de Rivoli', $results[3]['streetName']);
        $this->assertEquals('Paris', $results[3]['city']);
        $this->assertEquals('France', $results[3]['country']);
    }

    public function testGetGeocodedDataWithRealAddressReturnsMultilpleResults()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new CloudMadeProvider($this->getAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $results  = $provider->getGeocodedData('73 Boulevard Schuman');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(48.92846, $results[0]['latitude'], '', 0.001);
        $this->assertEquals(2.55019, $results[0]['longitude'], '', 0.001);
        $this->assertArrayHasKey('south', $results[0]['bounds']);
        $this->assertArrayHasKey('west', $results[0]['bounds']);
        $this->assertArrayHasKey('north', $results[0]['bounds']);
        $this->assertArrayHasKey('east', $results[0]['bounds']);
        $this->assertEquals(48.92633, $results[0]['bounds']['south'], '', 0.001);
        $this->assertEquals(2.54513, $results[0]['bounds']['west'], '', 0.001);
        $this->assertEquals(48.93074, $results[0]['bounds']['north'], '', 0.001);
        $this->assertEquals(2.5552, $results[0]['bounds']['east'], '', 0.001);
        $this->assertNull($results[0]['streetNumber']);
        $this->assertEquals('Boulevard Robert Schuman', $results[0]['streetName']);
        $this->assertNull($results[0]['zipcode']);
        $this->assertEquals('Montreuil', $results[0]['city']);
        $this->assertEquals('Ile-del-france', $results[0]['region']);
        $this->assertEquals('Ile-del-france', $results[0]['county']);
        $this->assertEquals('France', $results[0]['country']);

        // not provided
        $this->assertNull($results[0]['countryCode']);
        $this->assertNull($results[0]['timezone']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(49.64693, $results[1]['latitude'], '', 0.001);
        $this->assertEquals(5.99272, $results[1]['longitude'], '', 0.001);
        $this->assertArrayHasKey('south', $results[1]['bounds']);
        $this->assertArrayHasKey('west', $results[1]['bounds']);
        $this->assertArrayHasKey('north', $results[1]['bounds']);
        $this->assertArrayHasKey('east', $results[1]['bounds']);
        $this->assertEquals(49.64676, $results[1]['bounds']['south'], '', 0.001);
        $this->assertEquals(5.98893, $results[1]['bounds']['west'], '', 0.001);
        $this->assertEquals(49.65023, $results[1]['bounds']['north'], '', 0.001);
        $this->assertEquals(5.99711, $results[1]['bounds']['east'], '', 0.001);
        $this->assertNull($results[1]['streetNumber']);
        $this->assertEquals('Boulevard Robert Schuman', $results[1]['streetName']);
        $this->assertNull($results[1]['zipcode']);
        $this->assertEquals('Luxembourg', $results[1]['city']);
        $this->assertEquals('Luxembourg', $results[1]['region']);
        $this->assertEquals('Luxembourg', $results[1]['county']);
        $this->assertEquals('Luxembourg', $results[1]['country']);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(44.96087, $results[2]['latitude'], '', 0.001);
        $this->assertEquals(4.90654, $results[2]['longitude'], '', 0.001);
        $this->assertArrayHasKey('south', $results[2]['bounds']);
        $this->assertArrayHasKey('west', $results[2]['bounds']);
        $this->assertArrayHasKey('north', $results[2]['bounds']);
        $this->assertArrayHasKey('east', $results[2]['bounds']);
        $this->assertEquals(44.96025, $results[2]['bounds']['south'], '', 0.001);
        $this->assertEquals(4.90413, $results[2]['bounds']['west'], '', 0.001);
        $this->assertEquals(44.96109, $results[2]['bounds']['north'], '', 0.001);
        $this->assertEquals(4.90946, $results[2]['bounds']['east'], '', 0.001);
        $this->assertNull($results[2]['streetNumber']);
        $this->assertEquals('Boulevard Robert Schuman', $results[2]['streetName']);
        $this->assertNull($results[2]['zipcode']);
        $this->assertEquals('Bourg lès Valence', $results[2]['city']);
        $this->assertEquals('Rhone-alpes', $results[2]['region']);
        $this->assertEquals('Rhone-alpes', $results[2]['county']);
        $this->assertEquals('France', $results[2]['country']);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(44.96098, $results[3]['latitude'], '', 0.001);
        $this->assertEquals(4.90574, $results[3]['longitude'], '', 0.001);
        $this->assertArrayHasKey('south', $results[3]['bounds']);
        $this->assertArrayHasKey('west', $results[3]['bounds']);
        $this->assertArrayHasKey('north', $results[3]['bounds']);
        $this->assertArrayHasKey('east', $results[3]['bounds']);
        $this->assertEquals(44.96098, $results[3]['bounds']['south'], '', 0.001);
        $this->assertEquals(4.90563, $results[3]['bounds']['west'], '', 0.001);
        $this->assertEquals(44.96098, $results[3]['bounds']['north'], '', 0.001);
        $this->assertEquals(4.90585, $results[3]['bounds']['east'], '', 0.001);
        $this->assertNull($results[3]['streetNumber']);
        $this->assertEquals('Boulevard Robert Schuman', $results[3]['streetName']);
        $this->assertNull($results[3]['zipcode']);
        $this->assertEquals('Bourg lès Valence', $results[3]['city']);
        $this->assertEquals('Rhone-alpes', $results[3]['region']);
        $this->assertEquals('Rhone-alpes', $results[3]['county']);
        $this->assertEquals('France', $results[3]['country']);

        $this->assertInternalType('array', $results[4]);
        $this->assertEquals(50.29716, $results[4]['latitude'], '', 0.001);
        $this->assertEquals(2.77608, $results[4]['longitude'], '', 0.001);
        $this->assertArrayHasKey('south', $results[4]['bounds']);
        $this->assertArrayHasKey('west', $results[4]['bounds']);
        $this->assertArrayHasKey('north', $results[4]['bounds']);
        $this->assertArrayHasKey('east', $results[4]['bounds']);
        $this->assertEquals(50.29633, $results[4]['bounds']['south'], '', 0.001);
        $this->assertEquals(2.76961, $results[4]['bounds']['west'], '', 0.001);
        $this->assertEquals(50.29735, $results[4]['bounds']['north'], '', 0.001);
        $this->assertEquals(2.78268, $results[4]['bounds']['east'], '', 0.001);
        $this->assertNull($results[4]['streetNumber']);
        $this->assertEquals('Boulevard Robert Schuman', $results[4]['streetName']);
        $this->assertNull($results[4]['zipcode']);
        $this->assertEquals('Arras', $results[4]['city']);
        $this->assertEquals('Nord-pas-de-calais', $results[4]['region']);
        $this->assertEquals('Nord-pas-de-calais', $results[4]['county']);
        $this->assertEquals('France', $results[4]['country']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The CloudMadeProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv4()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new CloudMadeProvider($this->getAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $provider->getGeocodedData('88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The CloudMadeProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        if (!isset($_SERVER['CLOUDMADE_API_KEY'])) {
            $this->markTestSkipped('You need to configure the CLOUDMADE_API_KEY value in phpunit.xml');
        }

        $provider = new CloudMadeProvider($this->getAdapter(), $_SERVER['CLOUDMADE_API_KEY']);
        $provider->getGeocodedData('::ffff:88.188.221.14');
    }
}
