<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\ArcGISOnlineProvider;

class ArcGISOnlineProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('arcgis_online', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     */
    public function testGetGeocodedDataWithInvalidData()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapter());
        $provider->getGeocodedData('loremipsum');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Invalid address.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Invalid address.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The ArcGISOnlineProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The ArcGISOnlineProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $provider = new ArcGISOnlineProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertEquals(48.863279997000461, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.3890199980004354, $result['longitude'], '', 0.0001);
        $this->assertEquals('10 Avenue Gambetta, 75020, 20e Arrondissement, Paris', $result['streetName']);
        $this->assertEquals(75020, $result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('Île-de-France', $result['region']);
        $this->assertEquals('FRA', $result['countryCode']);
        $this->assertEquals(10, $result['streetNumber']);

        $this->assertNull($result['country']);
        $this->assertNull($result['timezone']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['countyCode']);
    }

    public function testGetGeocodedDataWithRealAddressAndHttps()
    {
        $provider = new ArcGISOnlineProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), null, true);
        $result   = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertEquals(48.863279997000461, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.3890199980004354, $result['longitude'], '', 0.0001);
        $this->assertEquals('10 Avenue Gambetta, 75020, 20e Arrondissement, Paris', $result['streetName']);
        $this->assertEquals(75020, $result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('Île-de-France', $result['region']);
        $this->assertEquals('FRA', $result['countryCode']);
        $this->assertEquals(10, $result['streetNumber']);

        $this->assertNull($result['country']);
        $this->assertNull($result['timezone']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['countyCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     */
    public function testGetGeocodedDataWithInvalidAddressForSourceCountry()
    {
        $provider = new ArcGISOnlineProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'USA');
        $result   = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     */
    public function testGetGeocodedDataWithInvalidAddressWithHttpsForSourceCountry()
    {
        $provider = new ArcGISOnlineProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'USA', true);
        $result   = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     */
    public function testGetReversedDataWithInvalid()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapter());
        $provider->getReversedData(array(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     */
    public function testGetReversedDataWithCoordinatesContentReturnNull()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapterReturns(null));
        $provider->getReversedData(array(48.863279997000461, 2.3890199980004354));
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        $provider = new ArcGISOnlineProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $provider->getReversedData(array(48.863279997000461, 2.3890199980004354));

        $this->assertEquals(48.863279997000461, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.3890199980004354, $result['longitude'], '', 0.0001);
        $this->assertEquals('10 Avenue Gambetta', $result['streetName']);
        $this->assertEquals(75020, $result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('Île-de-France', $result['region']);
        $this->assertEquals('FRA', $result['countryCode']);

        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['country']);
        $this->assertNull($result['timezone']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['countyCode']);
    }

    public function testGetReversedDataWithRealCoordinatesWithHttps()
    {
        $provider = new ArcGISOnlineProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), null, true);
        $result = $provider->getReversedData(array(48.863279997000461, 2.3890199980004354));

        $this->assertEquals(48.863279997000461, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.3890199980004354, $result['longitude'], '', 0.0001);
        $this->assertEquals('10 Avenue Gambetta', $result['streetName']);
        $this->assertEquals(75020, $result['zipcode']);
        $this->assertEquals('Paris', $result['city']);
        $this->assertEquals('Paris', $result['county']);
        $this->assertEquals('Île-de-France', $result['region']);
        $this->assertEquals('FRA', $result['countryCode']);

        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['country']);
        $this->assertNull($result['timezone']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['countyCode']);
    }

    public function testGetGeocodedDataWithCity()
    {
        $provider = new ArcGISOnlineProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $provider->getGeocodedData('Hannover');

        $this->assertEquals(52.370518568000477, $result['latitude'], '', 0.0001);
        $this->assertEquals(9.7332166860004463, $result['longitude'], '', 0.0001);
        $this->assertEquals('Hannover, Lower Saxony, Germany', $result['streetName']);
        $this->assertEquals('Lower Saxony', $result['region']);
        $this->assertEquals('DEU', $result['countryCode']);

        $this->assertNull($result['city']);
        $this->assertNull($result['county']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['country']);
        $this->assertNull($result['timezone']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['countyCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The ArcGISOnlineProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapter($this->never()));
        $result = $provider->getGeocodedData('88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The ArcGISOnlineProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapter($this->never()));
        $result = $provider->getGeocodedData('::ffff:88.188.221.14');
    }
}
