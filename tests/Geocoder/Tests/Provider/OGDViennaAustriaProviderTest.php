<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\OGDViennaAustriaProvider;

/**
 * @author Robert Harm <www.harm.co.at>
 * Data source: City of Vienna, http://data.wien.gv.at
 */
class OGDViennaAustriaProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new OGDViennaAustriaProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('ogd_vienna_austria', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://data.wien.gv.at/daten/OGDAddressService.svc/GetAddressInfo?CRS=EPSG:4326&Address=Stephansplatz
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new OGDViennaAustriaProvider($this->getMockAdapter());
        $provider->getGeocodedData('Stephansplatz');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://data.wien.gv.at/daten/OGDAddressService.svc/GetAddressInfo?CRS=EPSG:4326&Address=yyyyyyy
     */
    public function testGetGeocodedDataWithWrongAddress()
    {
        $provider = new OGDViennaAustriaProvider($this->getAdapter());
        $provider->getGeocodedData('yyyyyyy');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $provider = new OGDViennaAustriaProvider($this->getAdapter());
        $result   = $provider->getGeocodedData('Stephansplatz');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(48.208583576583, $result['latitude'], '', 0.0001);
        $this->assertEquals(16.373089928434, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        //$this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        //$this->assertNull($result['cityDistrict']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        //$this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The OGDViennaAustriaProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new OGDViennaAustriaProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The OGDViennaAustriaProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new OGDViennaAustriaProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The OGDViennaAustriaProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv4()
    {
        $provider = new OGDViennaAustriaProvider($this->getAdapter());
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The OGDViennaAustriaProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithIPv6()
    {
        $provider = new OGDViennaAustriaProvider($this->getAdapter());
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The OGDViennaAustriaProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new OGDViennaAustriaProvider($this->getMockAdapter($this->never()));
        $provider->getReversedData(array(1, 2));
    }
}
