<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GoogleMaps\Tests;

use Geocoder\Collection;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Tests\TestCase;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
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
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocode()
    {
        $provider = new GoogleMaps($this->getMockAdapter());
        $result = $provider->geocodeQuery(GeocodeQuery::create('foobar'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new GoogleMaps($this->getMockAdapter($this->never()));
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMaps provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new GoogleMaps($this->getMockAdapter($this->never()));
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The GoogleMaps provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithRealIp()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testGeocodeWithAddressGetsNullContent()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns(null));
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithAddressGetsEmptyContent()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns('{"status":"OK"}'));
        $result = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    /**
     * @expectedException \Geocoder\Exception\QuotaExceeded
     * @expectedExceptionMessage Daily quota exceeded https://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France
     */
    public function testGeocodeWithQuotaExceeded()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns('{"status":"OVER_QUERY_LIMIT"}'));
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithRealAddress()
    {
        $provider = new GoogleMaps($this->getAdapter(), 'Île-de-France');
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France')->withLocale('fr-FR'));

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
        $provider = new GoogleMaps($this->getAdapter(), null, null);
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));

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
        $results = $provider->geocodeQuery(GeocodeQuery::create('Paris, France'));

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
        $results = $provider->geocodeQuery(GeocodeQuery::create('Paris'));

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
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testReverse()
    {
        $provider = new GoogleMaps($this->getMockAdapter());
        $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));
    }

    public function testReverseWithRealCoordinates()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.8631507, 2.388911));

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
     * @expectedException \Geocoder\Exception\InvalidServerResponse
     */
    public function testReverseWithCoordinatesGetsNullContent()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns(null));
        $provider->reverseQuery(ReverseQuery::fromCoordinates(48.8631507, 2.388911));
    }

    public function testGeocodeWithCityDistrict()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('Kalbacher Hauptstraße 10, 60437 Frankfurt, Germany'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('Kalbach-Riedberg', $result->getSubLocality());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API key is invalid https://maps.googleapis.com/maps/api/geocode/json?address=10%20avenue%20Gambetta%2C%20Paris%2C%20France
     */
    public function testGeocodeWithInavlidApiKey()
    {
        $provider = new GoogleMaps($this->getMockAdapterReturns('{"error_message":"The provided API key is invalid.", "status":"REQUEST_DENIED"}'));
        $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));
    }

    public function testGeocodeWithRealValidApiKey()
    {
        if (!isset($_SERVER['GOOGLE_GEOCODING_KEY'])) {
            $this->markTestSkipped('You need to configure the GOOGLE_GEOCODING_KEY value in phpunit.xml');
        }

        $provider = new GoogleMaps($this->getAdapter($_SERVER['GOOGLE_GEOCODING_KEY']), null, $_SERVER['GOOGLE_GEOCODING_KEY']);

        $results = $provider->geocodeQuery(GeocodeQuery::create('Columbia University'));

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
        $provider = new GoogleMaps($this->getAdapter(), null, $this->testAPIKey);

        $provider->geocodeQuery(GeocodeQuery::create('Columbia University'));
    }

    public function testGeocodePostalTown()
    {
        $provider = new GoogleMaps($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('CF37, United Kingdom'));

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
            $provider->geocodeQuery(GeocodeQuery::create('blah'));
        } catch (InvalidServerResponse $e) {
        }
        $this->assertEquals('https://maps.googleapis.com/maps/api/geocode/json?address=blah&client=foo', $uri);
    }

    public function testBusinessQueryWithPrivateKey()
    {
        $uri = '';

        $provider = GoogleMaps::business(
            $this->getMockAdapterWithRequestCallback(
                function (RequestInterface $request) use (&$uri) {
                    $uri = (string) $request->getUri();
                }
            ),
            'foo',
            'bogus'
        );

        try {
            $provider->geocodeQuery(GeocodeQuery::create('blah'));
        } catch (InvalidServerResponse $e) {
        }
        $this->assertEquals(
            'https://maps.googleapis.com/maps/api/geocode/json?address=blah&client=foo&signature=9G2weMhhd4E2ciR681gp9YabvUg=',
            $uri
        );
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage Invalid client ID / API Key https://maps.googleapis.com/maps/api/geocode/json?address=Columbia%20University&client=foo&signature=9dJq1hPF7_iwafUpnqXUqEkP0gY=
     */
    public function testGeocodeWithInvalidClientIdAndKey()
    {
        $provider = GoogleMaps::business($this->getAdapter(), 'foo', 'bogus');
        $provider->geocodeQuery(GeocodeQuery::create('Columbia University'));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage Invalid client ID / API Key https://maps.googleapis.com/maps/api/geocode/json?address=Columbia%20University&client=foo&signature=9dJq1hPF7_iwafUpnqXUqEkP0gY=
     */
    public function testGeocodeWithInvalidClientIdAndKeyNoSsl()
    {
        $provider = GoogleMaps::business($this->getAdapter(), 'foo', 'bogus');
        $provider->geocodeQuery(GeocodeQuery::create('Columbia University'));
    }
}
