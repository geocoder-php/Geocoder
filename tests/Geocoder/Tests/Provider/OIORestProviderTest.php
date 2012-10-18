<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\OIORestProvider;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class OIORestProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new OIORestProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('oio_rest', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geo.oiorest.dk/adresser/.json
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new OIORestProvider($this->getMockAdapter());
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geo.oiorest.dk/adresser/.json
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new OIORestProvider($this->getMockAdapter());
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geo.oiorest.dk/adresser/Tagensvej%2C47%2C2200.json
     */
    public function testGetGeocodedDataWithAddressContentReturnNull()
    {
        $provider = new OIORestProvider($this->getMockAdapterGetContentReturnNull());
        $provider->getGeocodedData('Tagensvej 47, 2200 København N');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geo.oiorest.dk/adresser/Tagensvej%2C47%2C2200.json
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new OIORestProvider($this->getMockAdapter());
        $provider->getGeocodedData('Tagensvej 47, 2200 København N');
    }

    public function testGetGeocodedDataWithRealAddressOne()
    {
        $provider = new OIORestProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('Tagensvej 47, 2200 København N');

        $this->assertEquals(55.6999, $result['latitude'], '', 0.0001);
        $this->assertEquals(12.5527, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertEquals(47, $result['streetNumber']);
        $this->assertEquals('Tagensvej', $result['streetName']);
        $this->assertEquals(2200, $result['zipcode']);
        $this->assertEquals('København N', $result['city']);
        $this->assertEquals('København', $result['cityDistrict']);
        $this->assertEquals('Region Hovedstaden', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('Denmark', $result['country']);
        $this->assertEquals('DK', $result['countryCode']);
        $this->assertEquals('Europe/Copenhagen', $result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressTwo()
    {
        $provider = new OIORestProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('Lauritzens Plads 1, 9000 Aalborg');

        $this->assertEquals(57.0489, $result['latitude'], '', 0.0001);
        $this->assertEquals(9.94566, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertEquals(1, $result['streetNumber']);
        $this->assertEquals('Lauritzens Plads', $result['streetName']);
        $this->assertEquals(9000, $result['zipcode']);
        $this->assertEquals('Aalborg', $result['city']);
        $this->assertEquals('Aalborg', $result['cityDistrict']);
        $this->assertEquals('Region Nordjylland', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('Denmark', $result['country']);
        $this->assertEquals('DK', $result['countryCode']);
        $this->assertEquals('Europe/Copenhagen', $result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressThree()
    {
        $provider = new OIORestProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('St.Blichers Vej 74, 8210 Århus V');

        $this->assertEquals(56.1623, $result['latitude'], '', 0.0001);
        $this->assertEquals(10.1501, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertEquals(74, $result['streetNumber']);
        $this->assertEquals('St.Blichers Vej', $result['streetName']);
        $this->assertEquals(8210, $result['zipcode']);
        $this->assertEquals('Aarhus V', $result['city']);
        $this->assertEquals('Aarhus', $result['cityDistrict']);
        $this->assertEquals('Region Midtjylland', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('Denmark', $result['country']);
        $this->assertEquals('DK', $result['countryCode']);
        $this->assertEquals('Europe/Copenhagen', $result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressFour()
    {
        $provider = new OIORestProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('Århusgade 80, 2100 København Ø');

        $this->assertEquals(55.7063, $result['latitude'], '', 0.0001);
        $this->assertEquals(12.5837, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertEquals(80, $result['streetNumber']);
        $this->assertEquals('Århusgade', $result['streetName']);
        $this->assertEquals(2100, $result['zipcode']);
        $this->assertEquals('København Ø', $result['city']);
        $this->assertEquals('København', $result['cityDistrict']);
        $this->assertEquals('Region Hovedstaden', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('Denmark', $result['country']);
        $this->assertEquals('DK', $result['countryCode']);
        $this->assertEquals('Europe/Copenhagen', $result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressFive()
    {
        $provider = new OIORestProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('Hvenekildeløkken 255, 5240 Odense');

        $this->assertEquals(55.4221, $result['latitude'], '', 0.0001);
        $this->assertEquals(10.4588, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertEquals(255, $result['streetNumber']);
        $this->assertEquals('Hvenekildeløkken', $result['streetName']);
        $this->assertEquals(5240, $result['zipcode']);
        $this->assertEquals('Odense NØ', $result['city']);
        $this->assertEquals('Odense', $result['cityDistrict']);
        $this->assertEquals('Region Syddanmark', $result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertEquals('Denmark', $result['country']);
        $this->assertEquals('DK', $result['countryCode']);
        $this->assertEquals('Europe/Copenhagen', $result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The OIORestProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new OIORestProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The OIORestProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new OIORestProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The OIORestProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv4()
    {
        $provider = new OIORestProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The OIORestProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv6()
    {
        $provider = new OIORestProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The OIORestProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new OIORestProvider($this->getMockAdapter($this->never()));
        $provider->getReversedData(array(1, 2));
    }
}
