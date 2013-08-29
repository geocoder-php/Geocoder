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
     * @expectedExceptionMessage Could not execute query http://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/find?text=10+avenue+Gambetta%2C+Paris%2C+France&maxLocations=5&f=json&outFields=*
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $provider = new ArcGISOnlineProvider($this->getAdapter());
        $results  = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(48.863279997000461, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.3890199980004354, $result['longitude'], '', 0.0001);
        $this->assertEquals('10 Avenue Gambetta, 75020, Paris', $result['streetName']);
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
        $provider = new ArcGISOnlineProvider($this->getAdapter(), null, true);
        $results  = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(48.863279997000461, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.3890199980004354, $result['longitude'], '', 0.0001);
        $this->assertEquals('10 Avenue Gambetta, 75020, Paris', $result['streetName']);
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
     * @expectedExceptionMessage No results found for query http://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/find?text=10+avenue+Gambetta%2C+Paris%2C+France
     */
    public function testGetGeocodedDataWithInvalidAddressForSourceCountry()
    {
        $provider = new ArcGISOnlineProvider($this->getAdapter(), 'USA');
        $result   = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage No results found for query https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/find?text=10+avenue+Gambetta%2C+Paris%2C+France
     */
    public function testGetGeocodedDataWithInvalidAddressWithHttpsForSourceCountry()
    {
        $provider = new ArcGISOnlineProvider($this->getAdapter(), 'USA', true);
        $result   = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    /**
     * @expectedException Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/reverseGeocode?location=2.000000,1.000000&maxLocations=5&f=json&outFields=*
     */
    public function testGetReversedDataWithInvalid()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapter());
        $provider->getReversedData(array(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/reverseGeocode?location=2.389020,48.863280&maxLocations=5&f=json&outFields=*
     */
    public function testGetReversedDataWithCoordinatesContentReturnNull()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapterReturns(null));
        $provider->getReversedData(array(48.863279997000461, 2.3890199980004354));
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        $provider = new ArcGISOnlineProvider($this->getAdapter());
        $result   = $provider->getReversedData(array(48.863279997000461, 2.3890199980004354));

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertEquals(48.863279997000461, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.3890199980004354, $result['longitude'], '', 0.0001);
        $this->assertEquals('3 Avenue Gambetta', $result['streetName']);
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
        $provider = new ArcGISOnlineProvider($this->getAdapter(), null, true);
        $result   = $provider->getReversedData(array(48.863279997000461, 2.3890199980004354));

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertEquals(48.863279997000461, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.3890199980004354, $result['longitude'], '', 0.0001);
        $this->assertEquals('3 Avenue Gambetta', $result['streetName']);
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
        $provider = new ArcGISOnlineProvider($this->getAdapter());
        $results  = $provider->getGeocodedData('Hannover');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(52.370518568000477, $results[0]['latitude'], '', 0.0001);
        $this->assertEquals(9.7332166860004463, $results[0]['longitude'], '', 0.0001);
        $this->assertEquals('Hannover, Lower Saxony, Germany', $results[0]['streetName']);
        $this->assertEquals('Lower Saxony', $results[0]['region']);
        $this->assertEquals('DEU', $results[0]['countryCode']);

        $this->assertNull($results[0]['city']);
        $this->assertNull($results[0]['county']);
        $this->assertNull($results[0]['zipcode']);
        $this->assertNull($results[0]['streetNumber']);
        $this->assertNull($results[0]['country']);
        $this->assertNull($results[0]['timezone']);
        $this->assertNull($results[0]['regionCode']);
        $this->assertNull($results[0]['bounds']);
        $this->assertNull($results[0]['cityDistrict']);
        $this->assertNull($results[0]['countyCode']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(52.370518568, $results[1]['latitude'], '', 0.0001);
        $this->assertEquals(9.7332166860004, $results[1]['longitude'], '', 0.0001);
        $this->assertEquals('Hannover, Lower Saxony, Germany', $results[1]['streetName']);
        $this->assertEquals('Hannover', $results[1]['city']);
        $this->assertEquals('Lower Saxony', $results[1]['region']);
        $this->assertEquals('DEU', $results[1]['countryCode']);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(47.111386795, $results[2]['latitude'], '', 0.0001);
        $this->assertEquals(-101.426539157, $results[2]['longitude'], '', 0.0001);
        $this->assertEquals('Hannover, North Dakota, United States', $results[2]['streetName']);
        $this->assertNull($results[2]['city']);
        $this->assertEquals('North Dakota', $results[2]['region']);
        $this->assertEquals('USA', $results[2]['countryCode']);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(39.391768472, $results[3]['latitude'], '', 0.0001);
        $this->assertEquals(-77.440257129, $results[3]['longitude'], '', 0.0001);
        $this->assertEquals('Hannover, Maryland, United States', $results[3]['streetName']);
        $this->assertEquals('Maryland', $results[3]['region']);
        $this->assertEquals('USA', $results[3]['countryCode']);

        $this->assertInternalType('array', $results[4]);
        $this->assertEquals(53.174198173, $results[4]['latitude'], '', 0.0001);
        $this->assertEquals(8.5069383810005, $results[4]['longitude'], '', 0.0001);
        $this->assertEquals('Hannöver, Lower Saxony, Germany', $results[4]['streetName']);
        $this->assertNull($results[4]['city']);
        $this->assertEquals('Lower Saxony', $results[4]['region']);
        $this->assertNull($results[4]['county']);
        $this->assertEquals('DEU', $results[4]['countryCode']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The ArcGISOnlineProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv4()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('88.188.221.14');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The ArcGISOnlineProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithRealIPv6()
    {
        $provider = new ArcGISOnlineProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('::ffff:88.188.221.14');
    }
}
