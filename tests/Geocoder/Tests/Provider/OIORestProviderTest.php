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
     * @expectedExceptionMessage Could not execute query http://geo.oiorest.dk/adresser.json?q=
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new OIORestProvider($this->getMockAdapter());
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geo.oiorest.dk/adresser.json?q=
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new OIORestProvider($this->getMockAdapter());
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geo.oiorest.dk/adresser.json?q=Tagensvej%2047%2C%202200%20K%C3%B8benhavn%20N
     */
    public function testGetGeocodedDataWithAddressContentReturnNull()
    {
        $provider = new OIORestProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('Tagensvej 47, 2200 København N');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geo.oiorest.dk/adresser.json?q=Tagensvej%2047%2C%202200%20K%C3%B8benhavn%20N
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new OIORestProvider($this->getMockAdapter());
        $provider->getGeocodedData('Tagensvej 47, 2200 København N');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $provider = new OIORestProvider($this->getAdapter());
        $result   = $provider->getGeocodedData('Tagensvej 47 2200 København');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(55.6999, $result['latitude'], '', 0.0001);
        $this->assertEquals(12.5527, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertEquals(47, $result['streetNumber']);
        $this->assertEquals('Tagensvej', $result['streetName']);
        $this->assertEquals(2200, $result['zipcode']);
        $this->assertEquals('København N', $result['city']);
        $this->assertEquals('København', $result['cityDistrict']);
        $this->assertEquals('Region Hovedstaden', $result['region']);
        $this->assertEquals('1084', $result['regionCode']);
        $this->assertEquals('Denmark', $result['country']);
        $this->assertEquals('DK', $result['countryCode']);
        $this->assertEquals('Europe/Copenhagen', $result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressAalborg()
    {
        $provider = new OIORestProvider($this->getAdapter());
        $result   = $provider->getGeocodedData('Lauritzens Plads 1 9000 Aalborg');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(57.0489, $result['latitude'], '', 0.0001);
        $this->assertEquals(9.94566, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertEquals(1, $result['streetNumber']);
        $this->assertEquals('Lauritzens Plads', $result['streetName']);
        $this->assertEquals(9000, $result['zipcode']);
        $this->assertEquals('Aalborg', $result['city']);
        $this->assertEquals('Aalborg', $result['cityDistrict']);
        $this->assertEquals('Region Nordjylland', $result['region']);
        $this->assertEquals('1081', $result['regionCode']);
        $this->assertEquals('Denmark', $result['country']);
        $this->assertEquals('DK', $result['countryCode']);
        $this->assertEquals('Europe/Copenhagen', $result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressAarhus()
    {
        $provider = new OIORestProvider($this->getAdapter());
        $result   = $provider->getGeocodedData('St.Blichers Vej 74 8210 AArhus');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(56.1623, $result['latitude'], '', 0.0001);
        $this->assertEquals(10.1501, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertEquals(74, $result['streetNumber']);
        $this->assertEquals('St.Blichers Vej', $result['streetName']);
        $this->assertEquals(8210, $result['zipcode']);
        $this->assertEquals('Aarhus V', $result['city']);
        $this->assertEquals('Aarhus', $result['cityDistrict']);
        $this->assertEquals('Region Midtjylland', $result['region']);
        $this->assertEquals('1082', $result['regionCode']);
        $this->assertEquals('Denmark', $result['country']);
        $this->assertEquals('DK', $result['countryCode']);
        $this->assertEquals('Europe/Copenhagen', $result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressCopenhagen()
    {
        $provider = new OIORestProvider($this->getAdapter());
        $result   = $provider->getGeocodedData('Århusgade 80 2100 København');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(55.7063, $result['latitude'], '', 0.0001);
        $this->assertEquals(12.5837, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertEquals(80, $result['streetNumber']);
        $this->assertEquals('Århusgade', $result['streetName']);
        $this->assertEquals(2100, $result['zipcode']);
        $this->assertEquals('København Ø', $result['city']);
        $this->assertEquals('København', $result['cityDistrict']);
        $this->assertEquals('Region Hovedstaden', $result['region']);
        $this->assertEquals('1084', $result['regionCode']);
        $this->assertEquals('Denmark', $result['country']);
        $this->assertEquals('DK', $result['countryCode']);
        $this->assertEquals('Europe/Copenhagen', $result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressOdense()
    {
        $provider = new OIORestProvider($this->getAdapter());
        $result   = $provider->getGeocodedData('Hvenekildeløkken 255 5240 Odense');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(55.4221, $result['latitude'], '', 0.0001);
        $this->assertEquals(10.4588, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertEquals(255, $result['streetNumber']);
        $this->assertEquals('Hvenekildeløkken', $result['streetName']);
        $this->assertEquals(5240, $result['zipcode']);
        $this->assertEquals('Odense NØ', $result['city']);
        $this->assertEquals('Odense', $result['cityDistrict']);
        $this->assertEquals('Region Syddanmark', $result['region']);
        $this->assertEquals('1083', $result['regionCode']);
        $this->assertEquals('Denmark', $result['country']);
        $this->assertEquals('DK', $result['countryCode']);
        $this->assertEquals('Europe/Copenhagen', $result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressReturnsMultipleResults()
    {
        $provider = new OIORestProvider($this->getAdapter());
        $results  = $provider->getGeocodedData('Tagensvej 47');

        $this->assertInternalType('array', $results);
        $this->assertCount(10, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(55.6999504950464, $results[0]['latitude'], '', 0.0001);
        $this->assertEquals(12.552780016775, $results[0]['longitude'], '', 0.0001);
        $this->assertNull($results[0]['bounds']);
        $this->assertEquals(47, $results[0]['streetNumber']);
        $this->assertEquals('Tagensvej', $results[0]['streetName']);
        $this->assertEquals(2200, $results[0]['zipcode']);
        $this->assertEquals('København N', $results[0]['city']);
        $this->assertEquals('København', $results[0]['cityDistrict']);
        $this->assertEquals('Region Hovedstaden', $results[0]['region']);
        $this->assertEquals('1084', $results[0]['regionCode']);
        $this->assertEquals('Denmark', $results[0]['country']);
        $this->assertEquals('DK', $results[0]['countryCode']);
        $this->assertEquals('Europe/Copenhagen', $results[0]['timezone']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(55.2272287041871, $results[1]['latitude'], '', 0.000001);
        $this->assertEquals(11.7695695592728, $results[1]['longitude'], '', 0.000001);
        $this->assertEquals(1, $results[1]['streetNumber']);
        $this->assertEquals('Tagensvej', $results[1]['streetName']);
        $this->assertEquals(4700, $results[1]['zipcode']);
        $this->assertEquals('Næstved', $results[1]['city']);
        $this->assertEquals('Region Sjælland', $results[1]['region']);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(55.2271757871039, $results[2]['latitude'], '', 0.000001);
        $this->assertEquals(11.7691129123425, $results[2]['longitude'], '', 0.000001);
        $this->assertEquals(2, $results[2]['streetNumber']);
        $this->assertEquals('Tagensvej', $results[2]['streetName']);
        $this->assertEquals(4700, $results[2]['zipcode']);
        $this->assertEquals('Næstved', $results[2]['city']);
        $this->assertEquals('Region Sjælland', $results[2]['region']);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(55.2271205312374, $results[3]['latitude'], '', 0.000001);
        $this->assertEquals(11.7695192586423, $results[3]['longitude'], '', 0.000001);
        $this->assertEquals(3, $results[3]['streetNumber']);
        $this->assertEquals('Tagensvej', $results[3]['streetName']);
        $this->assertEquals(4700, $results[3]['zipcode']);
        $this->assertEquals('Næstved', $results[3]['city']);
        $this->assertEquals('Region Sjælland', $results[3]['region']);

        $this->assertInternalType('array', $results[4]);
        $this->assertEquals(55.2270592164468, $results[4]['latitude'], '', 0.000001);
        $this->assertEquals(11.7691790457091, $results[4]['longitude'], '', 0.000001);
        $this->assertEquals(4, $results[4]['streetNumber']);
        $this->assertEquals('Tagensvej', $results[4]['streetName']);
        $this->assertEquals(4700, $results[4]['zipcode']);
        $this->assertEquals('Næstved', $results[4]['city']);
        $this->assertEquals('Region Sjælland', $results[4]['region']);

        $this->assertInternalType('array', $results[5]);
        $this->assertEquals(55.2269838646556, $results[5]['latitude'], '', 0.000001);
        $this->assertEquals(11.7694569115436, $results[5]['longitude'], '', 0.000001);
        $this->assertEquals(5, $results[5]['streetNumber']);
        $this->assertEquals('Tagensvej', $results[5]['streetName']);
        $this->assertEquals(4700, $results[5]['zipcode']);
        $this->assertEquals('Næstved', $results[5]['city']);
        $this->assertEquals('Region Sjælland', $results[5]['region']);

        $this->assertInternalType('array', $results[6]);
        $this->assertEquals(55.2269514141865, $results[6]['latitude'], '', 0.000001);
        $this->assertEquals(11.7691124150561, $results[6]['longitude'], '', 0.000001);
        $this->assertEquals(6, $results[6]['streetNumber']);
        $this->assertEquals('Tagensvej', $results[6]['streetName']);
        $this->assertEquals(4700, $results[6]['zipcode']);
        $this->assertEquals('Næstved', $results[6]['city']);
        $this->assertEquals('Region Sjælland', $results[6]['region']);

        $this->assertInternalType('array', $results[7]);
        $this->assertEquals(55.2268810365838, $results[7]['latitude'], '', 0.000001);
        $this->assertEquals(11.7694245984061, $results[7]['longitude'], '', 0.000001);
        $this->assertEquals(7, $results[7]['streetNumber']);
        $this->assertEquals('Tagensvej', $results[7]['streetName']);
        $this->assertEquals(4700, $results[7]['zipcode']);
        $this->assertEquals('Næstved', $results[7]['city']);
        $this->assertEquals('Region Sjælland', $results[7]['region']);

        $this->assertInternalType('array', $results[8]);
        $this->assertEquals(55.2268597609547, $results[8]['latitude'], '', 0.000001);
        $this->assertEquals(11.7690947201688, $results[8]['longitude'], '', 0.000001);
        $this->assertEquals(8, $results[8]['streetNumber']);
        $this->assertEquals('Tagensvej', $results[8]['streetName']);
        $this->assertEquals(4700, $results[8]['zipcode']);
        $this->assertEquals('Næstved', $results[8]['city']);
        $this->assertEquals('Region Sjælland', $results[8]['region']);

        $this->assertInternalType('array', $results[9]);
        $this->assertEquals(55.2267671191869, $results[9]['latitude'], '', 0.000001);
        $this->assertEquals(11.7693738993126, $results[9]['longitude'], '', 0.000001);
        $this->assertEquals(9, $results[9]['streetNumber']);
        $this->assertEquals('Tagensvej', $results[9]['streetName']);
        $this->assertEquals(4700, $results[9]['zipcode']);
        $this->assertEquals('Næstved', $results[9]['city']);
        $this->assertEquals('Region Sjælland', $results[9]['region']);
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
        $provider = new OIORestProvider($this->getAdapter());
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The OIORestProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv6()
    {
        $provider = new OIORestProvider($this->getAdapter());
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geo.oiorest.dk/adresser/1.000000,2.000000.json
     */
    public function testGetReverseData()
    {
        $provider = new OIORestProvider($this->getMockAdapter());
        $provider->getReversedData(array(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geo.oiorest.dk/adresser/60.453947,22.256784.json
     */
    public function testGetReversedDataWithCoordinatesGetsNullContent()
    {
        $provider = new OIORestProvider($this->getMockAdapterReturns(null));
        $provider->getReversedData(array('60.4539471728726', '22.2567841926781'));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geo.oiorest.dk/adresser/60.453947,22.256784.json
     */
    public function testGetReversedDataWithCoordinatesGetsEmptyContent()
    {
        $provider = new OIORestProvider($this->getMockAdapterReturns(''));
        $provider->getReversedData(array('60.4539471728726', '22.2567841926781'));
    }

    public function testGetGeocodedDataWithRealCoordinates()
    {
        $provider = new OIORestProvider($this->getAdapter());
        $result = $provider->getReversedData(array(56.5231, 10.0659));

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(56.521542795662, $result['latitude'], '', 0.0001);
        $this->assertEquals(10.0668558607917, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertEquals(11, $result['streetNumber']);
        $this->assertEquals('Stabelsvej', $result['streetName']);
        $this->assertEquals(8981, $result['zipcode']);
        $this->assertEquals('Spentrup', $result['city']);
        $this->assertEquals('Randers', $result['cityDistrict']);
        $this->assertEquals('Region Midtjylland', $result['region']);
        $this->assertEquals('1082', $result['regionCode']);
        $this->assertEquals('Denmark', $result['country']);
        $this->assertEquals('DK', $result['countryCode']);
        $this->assertEquals('Europe/Copenhagen', $result['timezone']);
    }
}
