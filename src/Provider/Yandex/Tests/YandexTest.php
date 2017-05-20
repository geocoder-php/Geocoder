<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Yandex\Tests;

use Geocoder\Collection;
use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Tests\TestCase;
use Geocoder\Provider\Yandex\Yandex;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class YandexTest extends TestCase
{
    public function testGetName()
    {
        $provider = new Yandex($this->getMockAdapter($this->never()));
        $this->assertEquals('yandex', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Yandex provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new Yandex($this->getMockAdapter($this->never()));
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Yandex provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new Yandex($this->getMockAdapter($this->never()));
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithEmpty()
    {
        $provider = new Yandex($this->getMockAdapterReturns('{"error":{"status":"400","message":"missing geocode parameter"}}'));
        $result = $provider->geocodeQuery(GeocodeQuery::create('xx'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGeocodeWithInvalidData()
    {
        $provider = new Yandex($this->getMockAdapter());
        $result = $provider->geocodeQuery(GeocodeQuery::create('foobar'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGeocodeWithAddressGetsNullContent()
    {
        $provider = new Yandex($this->getMockAdapterReturns(null));
        $result = $provider->geocodeQuery(GeocodeQuery::create('Kabasakal Caddesi, Istanbul, Turkey'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGeocodeWithFakeAddress()
    {
        $provider = new Yandex($this->getAdapter());
        $result = $provider->geocodeQuery(GeocodeQuery::create('foobar'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGeocodeWithRealAddress()
    {
        $provider = new Yandex($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.863277, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.389016, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.861926, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(2.386967, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(48.864629, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(2.391064, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('Париж', $result->getLocality());
        $this->assertEquals('XX округ', $result->getSubLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Париж', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Иль-Де-Франс', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Франция', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getAdminLevels()->get(2)->getCode());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealAddressWithUALocale()
    {
        $provider = new Yandex($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('Copenhagen, Denmark')->withLocale('uk-UA'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(55.675676, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(12.567593, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(55.614999, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(12.45295, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(55.73259, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(12.65075, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals('Копенгаген', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Столичная область', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Данія', $result->getCountry()->getName());
        $this->assertEquals('DK', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());

        /** @var Location $result */
        $result = $results->get(1);
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(55.455739, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(9.972854, $result->getCoordinates()->getLongitude(), '', 0.01);

        /** @var Location $result */
        $result = $results->get(2);
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(55.713258, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(12.534930, $result->getCoordinates()->getLongitude(), '', 0.01);

        /** @var Location $result */
        $result = $results->get(3);
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(55.698878, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(12.578211, $result->getCoordinates()->getLongitude(), '', 0.01);

        /** @var Location $result */
        $result = $results->get(4);
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(55.690380, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(12.554827, $result->getCoordinates()->getLongitude(), '', 0.01);
    }

    public function testGeocodeWithRealAddressWithUSLocale()
    {
        $provider = new Yandex($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('1600 Pennsylvania Ave, Washington')->withLocale('en-US'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(38.897695, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(-77.038692, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(38.891265, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(-77.046921, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(38.904125, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(-77.030464, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals(1600, $result->getStreetNumber());
        $this->assertEquals('Pennsylvania Ave NW', $result->getStreetName());
        $this->assertEquals('Washington', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('District of Columbia', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('District of Columbia', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealAddressWithBYLocale()
    {
        $provider = new Yandex($this->getAdapter());
        $results = $provider->geocodeQuery(GeocodeQuery::create('ул.Ленина, 19, Минск 220030, Республика Беларусь')->withLocale('be-BY'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(53.898077, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(27.563673, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(53.896867, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(27.561624, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(53.899286, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(27.565721, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals(19, $result->getStreetNumber());
        $this->assertEquals('улица Ленина', $result->getStreetName());
        $this->assertEquals('Минск', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Минск', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Беларусь', $result->getCountry()->getName());
        $this->assertEquals('BY', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getTimezone());
    }

    public function testReverse()
    {
        $provider = new Yandex($this->getMockAdapter());
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(1, 2));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testReverseWithInvalidData()
    {
        $provider = new Yandex($this->getMockAdapter());
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates('foo', 'bar'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testReverseWithAddressGetsNullContent()
    {
        $provider = new Yandex($this->getMockAdapterReturns(null));
        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.863216489553, 2.388771995902061));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testReverseWithRealCoordinates()
    {
        $provider = new Yandex($this->getAdapter());
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.863216489553, 2.388771995902061));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.863212, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.388773, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.86294, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(2.387497, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(48.877038, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(2.406587, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('Париж', $result->getLocality());
        $this->assertEquals('XX округ', $result->getSubLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Париж', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Иль-Де-Франс', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Франция', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getAdminLevels()->get(2)->getCode());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());

        /** @var Location $result */
        $result = $results->get(1);
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.864848, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.3993549, $result->getCoordinates()->getLongitude(), '', 0.01);

        /** @var Location $result */
        $result = $results->get(2);
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.856929, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.341197, $result->getCoordinates()->getLongitude(), '', 0.01);
    }

    public function testReverseWithRealCoordinatesWithUSLocaleAndStreeToponym()
    {
        $provider = new Yandex($this->getAdapter(), 'street');
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.863216489553, 2.388771995902061)->withLocale('en-US'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.87132, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.404017, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(48.86294, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(2.387497, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(48.877038, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(2.406587, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('20e Arrondissement', $result->getSubLocality());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Paris', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Ile-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getAdminLevels()->get(2)->getCode());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());

        /** @var Location $result */
        $result = $results->get(1);
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.863230, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.388261, $result->getCoordinates()->getLongitude(), '', 0.01);

        /** @var Location $result */
        $result = $results->get(2);
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.866022, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.389662, $result->getCoordinates()->getLongitude(), '', 0.01);

        /** @var Location $result */
        $result = $results->get(3);
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.863918, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.387767, $result->getCoordinates()->getLongitude(), '', 0.01);

        /** @var Location $result */
        $result = $results->get(4);
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.863787, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(2.389600, $result->getCoordinates()->getLongitude(), '', 0.01);
    }

    public function testReverseWithRealCoordinatesWithUALocaleAndHouseToponym()
    {
        $provider = new Yandex($this->getAdapter(), 'house');
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(60.4539471768582, 22.2567842183875)->withLocale('uk-UA'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(60.454462, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(22.256561, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(60.45345, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(22.254513, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(60.455474, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(22.258609, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals(36, $result->getStreetNumber());
        $this->assertEquals('Bangårdsgatan', $result->getStreetName());
        $this->assertEquals('Турку', $result->getLocality());
        $this->assertEquals('Кескуста', $result->getSubLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Исконная Финляндия', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('Юго-Западная Финляндия', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Фінляндія', $result->getCountry()->getName());
        $this->assertEquals('FI', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getAdminLevels()->get(2)->getCode());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testReverseWithRealCoordinatesWithTRLocaleAndLocalityToponym()
    {
        $provider = new Yandex($this->getAdapter(), 'locality');
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(40.900640, 29.198184)->withLocale('tr-TR'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(40.874651, $result->getCoordinates()->getLatitude(), '', 0.01);
        $this->assertEquals(29.129562, $result->getCoordinates()->getLongitude(), '', 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEquals(40.860413, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(29.107230, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(40.876111, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(29.139021, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Büyükada', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Adalar', $result->getAdminLevels()->get(2)->getName());
        $this->assertEquals('İstanbul', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Türkiye', $result->getCountry()->getName());
        $this->assertEquals('TR', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getAdminLevels()->get(2)->getCode());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());
    }
}
