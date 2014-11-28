<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\GoogleMaps;

class GoogleMapsTest extends TestCase
{
    /**
     * @var string
     */
    private $testAPIKey = 'fake_key';

    public function testGetName()
    {
        $provider = new GoogleMaps($this->getMockAdapter($this->never()));
        $this->assertEquals('google_maps', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=foobar".
     */
    public function testGetGeocodedData()
    {
        $provider = new GoogleMaps($this->getMockAdapter());
        $provider->geocode('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=".
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new GoogleMaps($this->getMockAdapter());
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=".
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new GoogleMaps($this->getMockAdapter());
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMaps provider does not support IP addresses, only street addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new GoogleMaps($this->getMockAdapter($this->never()));
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMaps provider does not support IP addresses, only street addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new GoogleMaps($this->getMockAdapter($this->never()));
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMaps provider does not support IP addresses, only street addresses.
     */
    public function testGetGeocodedDataWithRealIp()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France".
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns(null));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France".
     */
    public function testGetGeocodedDataWithAddressGetsEmptyContent()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns('{"status":"OK"}'));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceeded
     * @expectedExceptionMessage Daily quota exceeded http://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France
     */
    public function testGetGeocodedDataWithQuotaExceeded()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns('{"status":"OVER_QUERY_LIMIT"}'));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $provider = new GoogleMaps($this->getAdapter(), 'fr-FR', 'Île-de-France');
        $results  = $provider->geocode('10 avenue Gambetta, Paris, France');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.8630462, $result->getLatitude(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getLongitude(), '', 0.001);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(48.8630462, $result->getBounds()->getSouth(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getBounds()->getWest(), '', 0.001);
        $this->assertEquals(48.8630462, $result->getBounds()->getNorth(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getBounds()->getEast(), '', 0.001);
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('Paris', $result->getCounty()->getName());
        $this->assertEquals('Île-de-France', $result->getRegion()->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getTimezone());
    }

    public function testGetGeocodedDataWithRealAddressWithSsl()
    {
        $provider = new GoogleMaps($this->getAdapter(), null, null, true);
        $results  = $provider->geocode('10 avenue Gambetta, Paris, France');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.8630462, $result->getLatitude(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getLongitude(), '', 0.001);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(48.8630462, $result->getBounds()->getSouth(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getBounds()->getWest(), '', 0.001);
        $this->assertEquals(48.8630462, $result->getBounds()->getNorth(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getBounds()->getEast(), '', 0.001);
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('Paris', $result->getCounty()->getName());
        $this->assertEquals('Île-de-France', $result->getRegion()->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getTimezone());
    }

    public function testGetGeocodedDataBoundsWithRealAddressForNonRooftopLocation()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $results  = $provider->geocode('Paris, France');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(48.815573, $result->getBounds()->getSouth(), '', 0.0001);
        $this->assertEquals(2.224199, $result->getBounds()->getWest(), '', 0.0001);
        $this->assertEquals(48.902145, $result->getBounds()->getNorth(), '', 0.0001);
        $this->assertEquals(2.4699209, $result->getBounds()->getEast(), '', 0.0001);
    }

    public function testGetGeocodedDataWithRealAddressReturnsMultipleResults()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $results  = $provider->geocode('Paris');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.856614, $result->getLatitude(), '', 0.001);
        $this->assertEquals(2.3522219, $result->getLongitude(), '', 0.001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        /** @var \Geocoder\Model\Address $result */
        $result = $results[1];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(33.6609389, $result->getLatitude(), '', 0.001);
        $this->assertEquals(-95.555513, $result->getLongitude(), '', 0.001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var \Geocoder\Model\Address $result */
        $result = $results[2];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(36.3020023, $result->getLatitude(), '', 0.001);
        $this->assertEquals(-88.3267107, $result->getLongitude(), '', 0.001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var \Geocoder\Model\Address $result */
        $result = $results[3];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(39.611146, $result->getLatitude(), '', 0.001);
        $this->assertEquals(-87.6961374, $result->getLongitude(), '', 0.001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var \Geocoder\Model\Address $result */
        $result = $results[4];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(38.2097987, $result->getLatitude(), '', 0.001);
        $this->assertEquals(-84.2529869, $result->getLongitude(), '', 0.001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('United States',$result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=1.000000%2C2.000000".
     */
    public function testGetReversedData()
    {
        $provider = new GoogleMaps($this->getMockAdapter());
        $provider->reverse(1, 2);
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $results  = $provider->reverse(48.8631507, 2.388911);

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(1, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('Paris', $result->getCounty()->getName());
        $this->assertEquals('Île-de-France', $result->getRegion()->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=48.863151%2C2.388911".
     */
    public function testGetReversedDataWithCoordinatesGetsNullContent()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns(null));
        $provider->reverse(48.8631507, 2.388911);
    }

    public function testGetGeocodedDataWithCityDistrict()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $results  = $provider->geocode('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Kalbach-Riedberg', $result->getSubLocality());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API key is invalid http://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France
     */
    public function testGetGeocodedDataWithInavlidApiKey()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns('{"error_message":"The provided API key is invalid.", "status":"REQUEST_DENIED"}'));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithRealValidApiKey()
    {
        if (!isset($_SERVER['GOOGLE_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the GOOGLE_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new GoogleMaps($this->getAdapter(), null, null, true, $_SERVER['GOOGLE_GEOCODING_KEY']);

        $results = $provider->geocode('Columbia University');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNotNull($result->getLatitude());
        $this->assertNotNull($result->getLongitude());
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals('New York', $result->getLocality());
        $this->assertEquals('Manhattan', $result->getSubLocality());
        $this->assertEquals('New York', $result->getRegion()->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API key is invalid https://maps.googleapis.com/maps/api/geocode/json?address=Columbia%20University&key=fake_key
     */
    public function testGetGeocodedDataWithRealInvalidApiKey()
    {
        $provider = new GoogleMaps($this->getAdapter(), null, null, true, $this->testAPIKey);

        $provider->geocode('Columbia University');
    }
}
