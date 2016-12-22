<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Exception\NoResult;
use Geocoder\Location;
use Geocoder\Tests\TestCase;
use Geocoder\Provider\GoogleMaps;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;

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
    public function testGeocode()
    {
        $provider = new GoogleMaps($this->getMockAdapter());
        $provider->geocode('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=".
     */
    public function testGeocodeWithNull()
    {
        $provider = new GoogleMaps($this->getMockAdapter());
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=".
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new GoogleMaps($this->getMockAdapter());
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMaps provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new GoogleMaps($this->getMockAdapter($this->never()));
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMaps provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new GoogleMaps($this->getMockAdapter($this->never()));
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMaps provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIp()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France".
     */
    public function testGeocodeWithAddressGetsNullContent()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns(null));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France".
     */
    public function testGeocodeWithAddressGetsEmptyContent()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns('{"status":"OK"}'));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceeded
     * @expectedExceptionMessage Daily quota exceeded http://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France
     */
    public function testGeocodeWithQuotaExceeded()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns('{"status":"OVER_QUERY_LIMIT"}'));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGeocodeWithRealAddress()
    {
        $provider = new GoogleMaps($this->getAdapter(), 'fr-FR', 'Île-de-France');
        $results  = $provider->geocode('10 avenue Gambetta, Paris, France');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.8630462, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.8630462, $result->getBounds()->getSouth(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getBounds()->getWest(), '', 0.001);
        $this->assertEquals(48.8630462, $result->getBounds()->getNorth(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getBounds()->getEast(), '', 0.001);
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Île-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealAddressWithSsl()
    {
        $provider = new GoogleMaps($this->getAdapter(), null, null, true);
        $results  = $provider->geocode('10 avenue Gambetta, Paris, France');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.8630462, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.8630462, $result->getBounds()->getSouth(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getBounds()->getWest(), '', 0.001);
        $this->assertEquals(48.8630462, $result->getBounds()->getNorth(), '', 0.001);
        $this->assertEquals(2.3882487, $result->getBounds()->getEast(), '', 0.001);
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Île-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeBoundsWithRealAddressForNonRooftopLocation()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $results  = $provider->geocode('Paris, France');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.815573, $result->getBounds()->getSouth(), '', 0.0001);
        $this->assertEquals(2.224199, $result->getBounds()->getWest(), '', 0.0001);
        $this->assertEquals(48.902145, $result->getBounds()->getNorth(), '', 0.0001);
        $this->assertEquals(2.4699209, $result->getBounds()->getEast(), '', 0.0001);
    }

    public function testGeocodeWithRealAddressReturnsMultipleResults()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $results  = $provider->geocode('Paris');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(48.856614, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(2.3522219, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(1);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(33.6609389, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-95.555513, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(2);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(36.3020023, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-88.3267107, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(3);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(39.611146, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-87.6961374, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        /** @var Location $result */
        $result = $results->get(4);
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(38.2097987, $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals(-84.2529869, $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertEquals('United States',$result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?latlng=1.000000,2.000000".
     */
    public function testReverse()
    {
        $provider = new GoogleMaps($this->getMockAdapter());
        $provider->reverse(1, 2);
    }

    public function testReverseWithRealCoordinates()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $results  = $provider->reverse(48.8631507, 2.388911);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(12, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals(75020, $result->getPostalCode());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Île-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://maps.googleapis.com/maps/api/geocode/json?latlng=48.863151,2.388911".
     */
    public function testReverseWithCoordinatesGetsNullContent()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns(null));
        $provider->reverse(48.8631507, 2.388911);
    }

    public function testGeocodeWithCityDistrict()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $results  = $provider->geocode('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Kalbach-Riedberg', $result->getSubLocality());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API key is invalid http://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France
     */
    public function testGeocodeWithInavlidApiKey()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns('{"error_message":"The provided API key is invalid.", "status":"REQUEST_DENIED"}'));
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGeocodeWithRealValidApiKey()
    {
        if (!isset($_SERVER['GOOGLE_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the GOOGLE_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new GoogleMaps($this->getAdapter($_SERVER['GOOGLE_GEOCODING_KEY']), null, null, true, $_SERVER['GOOGLE_GEOCODING_KEY']);

        $results = $provider->geocode('Columbia University');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNotNull($result->getCoordinates()->getLatitude());
        $this->assertNotNull($result->getCoordinates()->getLongitude());
        $this->assertNotNull($result->getBounds());
        $this->assertEquals('New York', $result->getLocality());
        $this->assertEquals('Manhattan', $result->getSubLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('New York', $result->getAdminLevels()->get(1)->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API key is invalid https://maps.googleapis.com/maps/api/geocode/json?address=Columbia%20University&key=fake_key
     */
    public function testGeocodeWithRealInvalidApiKey()
    {
        $provider = new GoogleMaps($this->getAdapter(), null, null, true, $this->testAPIKey);

        $provider->geocode('Columbia University');
    }

    public function testGeocodePostalTown()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $results  = $provider->geocode('CF37, United Kingdom');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Pontypridd', $result->getLocality());
    }

    public function testBusinessQueryWithoutPrivateKey()
    {
        $uri = '';

        $provider = GoogleMaps::business(
            $this->getMockAdapterWithRequestCallback(
                function (RequestInterface $request) use (&$uri) {
                    $uri = $request->getUri();
                }
            ),
            'foo'
        );

        try {
            $provider->geocode('blah');
        } catch (NoResult $e) {
        }

        $this->assertEquals('http://maps.googleapis.com/maps/api/geocode/json?address=blah&client=foo', $uri);
    }

    public function testBusinessQueryWithPrivateKey()
    {
        $uri = '';

        $provider = GoogleMaps::business(
            $this->getMockAdapterWithRequestCallback(
                function (RequestInterface $request) use (&$uri) {
                    $uri = (string)$request->getUri();
                }
            ),
            'foo',
            'bogus'
        );

        try {
            $provider->geocode('blah');
        } catch (NoResult $e) {
        }

        $this->assertEquals(
            'http://maps.googleapis.com/maps/api/geocode/json?address=blah&client=foo&signature=9G2weMhhd4E2ciR681gp9YabvUg=',
            $uri
        );
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage Invalid client ID / API Key https://maps.googleapis.com/maps/api/geocode/json?address=Columbia%20University&client=foo&signature=9dJq1hPF7_iwafUpnqXUqEkP0gY=
     */
    public function testGeocodeWithInvalidClientIdAndKey()
    {
        $provider = GoogleMaps::business($this->getAdapter(), 'foo', 'bogus', null, null, true);
        $provider->geocode('Columbia University');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage Invalid client ID / API Key http://maps.googleapis.com/maps/api/geocode/json?address=Columbia%20University&client=foo&signature=9dJq1hPF7_iwafUpnqXUqEkP0gY=
     */
    public function testGeocodeWithInvalidClientIdAndKeyNoSsl()
    {
        $provider = GoogleMaps::business($this->getAdapter(), 'foo', 'bogus', null, null, false);
        $provider->geocode('Columbia University');
    }
}
