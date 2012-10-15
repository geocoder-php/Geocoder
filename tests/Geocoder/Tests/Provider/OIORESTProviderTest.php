<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;

use Geocoder\Provider\OIORESTProvider;

class OIORESTProviderTest extends TestCase
{
    public function testGetGeocodedDataWithNull()
    {
        $this->provider = new OIORESTProvider($this->getMockAdapter());
        $result = $this->provider->getGeocodedData(null);

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithEmpty()
    {
        $this->provider = new OIORESTProvider($this->getMockAdapter());
        $result = $this->provider->getGeocodedData('');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithAddress()
    {
        $this->provider = new OIORESTProvider($this->getMockAdapter());
        $result = $this->provider->getGeocodedData('Tagensvej 47, 2200 København N');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressWithSocketAdapter()
    {
        $this->provider = new OIORESTProvider(new \Geocoder\HttpAdapter\SocketAdapter());
        $result = $this->provider->getGeocodedData('Tagensvej 47, 2200 København N');

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

    public function testGetGeocodedDataWithRealAddressWithCurlHttpAdapter()
    {
        $this->provider = new OIORESTProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $this->provider->getGeocodedData('Lauritzens Plads 1, 9000 Aalborg');

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

    public function testGetGeocodedDataWithRealAddressWithBuzzHttpAdapter()
    {
        $this->provider = new OIORESTProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter());
        $result = $this->provider->getGeocodedData('St.Blichers Vej 74, 8210 Århus V');

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

    public function testGetGeocodedDataWithRealAddressWithGuzzleHttpAdapter()
    {
        $this->provider = new OIORESTProvider(new \Geocoder\HttpAdapter\GuzzleHttpAdapter());
        $result = $this->provider->getGeocodedData('Århusgade 80, 2100 København Ø');

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

    public function testGetGeocodedDataWithRealAddressWithZendHttpAdapter()
    {
        $this->provider = new OIORESTProvider(new \Geocoder\HttpAdapter\ZendHttpAdapter());
        $result = $this->provider->getGeocodedData('Hvenekildeløkken 255, 5240 Odense');

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
     */
    public function testGetGeocodedDataWithLocalhost()
    {
        $this->provider = new OIORESTProvider($this->getMockAdapter($this->never()));
        $this->provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     */
    public function testGetGeocodedDataWithIp()
    {
        $this->provider = new OIORESTProvider(new \Geocoder\HttpAdapter\BuzzHttpAdapter());
        $this->provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     */
    public function testGetReverseData()
    {
        $this->provider = new OIORESTProvider($this->getMockAdapter($this->never()));
        $this->provider->getReversedData(array(1, 2));
    }
}
