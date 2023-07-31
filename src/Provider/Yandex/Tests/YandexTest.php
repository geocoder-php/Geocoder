<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Yandex\Tests;

use Geocoder\Collection;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Provider\Yandex\Model\YandexAddress;
use Geocoder\Provider\Yandex\Yandex;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class YandexTest extends BaseTestCase
{
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $provider = new Yandex($this->getMockedHttpClient());
        $this->assertEquals('yandex', $provider->getName());
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Yandex provider does not support IP addresses, only street addresses.');

        $provider = new Yandex($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));
    }

    public function testGeocodeWithLocalhostIPv6(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The Yandex provider does not support IP addresses, only street addresses.');

        $provider = new Yandex($this->getMockedHttpClient());
        $provider->geocodeQuery(GeocodeQuery::create('::1'));
    }

    public function testGeocodeWithEmpty(): void
    {
        $provider = new Yandex($this->getMockedHttpClient('{"error":{"status":"400","message":"missing geocode parameter"}}'));
        $result = $provider->geocodeQuery(GeocodeQuery::create('xx'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGeocodeWithFakeAddress(): void
    {
        $provider = new Yandex($this->getHttpClient());
        $result = $provider->geocodeQuery(GeocodeQuery::create('foobar'));

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGeocodeWithRealAddress(): void
    {
        $provider = new Yandex($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('10 avenue Gambetta, Paris, France'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var YandexAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(48.863277, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.389016, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(48.861926, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(2.386967, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(48.864629, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(2.391064, $result->getBounds()->getEast(), 0.01);
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('Париж', $result->getLocality());
        $this->assertEquals('XX округ Парижа', $result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Иль-де-Франс', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Франция', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
        $this->assertEquals('exact', $result->getPrecision());
        $this->assertEquals('house', $result->getKind());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealAddressWithUALocale(): void
    {
        $provider = new Yandex($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('Copenhagen, Denmark')->withLocale('uk-UA'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(3, $results);

        /** @var YandexAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(55.675676, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(12.585828, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(55.614999, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(12.45295, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(55.73259, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(12.65075, $result->getBounds()->getEast(), 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals('Копенгаген', $result->getLocality());
        $this->assertCount(2, $result->getAdminLevels());
        $this->assertEquals('Столичная область', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Данія', $result->getCountry()->getName());
        $this->assertEquals('DK', $result->getCountry()->getCode());
        $this->assertEquals('other', $result->getPrecision());
        $this->assertEquals('Копенгаген', $result->getName());
        $this->assertEquals('locality', $result->getKind());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());

        /** @var YandexAddress $result */
        $result = $results->get(1);
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(55.716853, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(12.463837, $result->getCoordinates()->getLongitude(), 0.01);

        /** @var YandexAddress $result */
        $result = $results->get(2);
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(55.590338, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(12.130041, $result->getCoordinates()->getLongitude(), 0.01);
    }

    public function testGeocodeWithRealAddressWithUSLocale(): void
    {
        $provider = new Yandex($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('1600 Pennsylvania Ave, Washington')->withLocale('en-US'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var YandexAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(38.897695, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(-77.038692, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(38.891265, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(-77.058105, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(38.904125, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(-77.012426, $result->getBounds()->getEast(), 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Pennsylvania Avenue Northwest', $result->getStreetName());
        $this->assertEquals('City of Washington', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('District of Columbia', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('United States of America', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('street', $result->getPrecision());
        $this->assertEquals('street', $result->getKind());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealAddressWithBYLocale(): void
    {
        $provider = new Yandex($this->getHttpClient());
        $results = $provider->geocodeQuery(GeocodeQuery::create('ул.Ленина, 19, Минск 220030, Республика Беларусь')->withLocale('be-BY'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var YandexAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(53.898077, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(27.563673, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(53.896867, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(27.561624, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(53.899286, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(27.565721, $result->getBounds()->getEast(), 0.01);
        $this->assertEquals(19, $result->getStreetNumber());
        $this->assertEquals('вуліца Леніна', $result->getStreetName());
        $this->assertEquals('Мінск', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Мінск', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Беларусь', $result->getCountry()->getName());
        $this->assertEquals('BY', $result->getCountry()->getCode());
        $this->assertEquals('exact', $result->getPrecision());
        $this->assertEquals('house', $result->getKind());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getTimezone());
    }

    public function testReverseWithRealCoordinates(): void
    {
        $provider = new Yandex($this->getHttpClient());
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.863216489553, 2.388771995902061));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var YandexAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(48.863212, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.388773, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(48.86294, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(2.387497, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(48.866063, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(2.392833, $result->getBounds()->getEast(), 0.01);
        $this->assertEquals(1, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('Париж', $result->getLocality());
        $this->assertEquals('XX округ Парижа', $result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Иль-де-Франс', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Франция', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
        $this->assertEquals('exact', $result->getPrecision());
        $this->assertEquals('Avenue Gambetta, 1', $result->getName());
        $this->assertEquals('house', $result->getKind());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());

        /** @var YandexAddress $result */
        $result = $results->get(1);
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(48.864848, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.3993549, $result->getCoordinates()->getLongitude(), 0.01);

        /** @var YandexAddress $result */
        $result = $results->get(2);
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(48.856929, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.392115, $result->getCoordinates()->getLongitude(), 0.01);
    }

    public function testReverseWithRealCoordinatesWithUSLocaleAndStreeToponym(): void
    {
        $provider = new Yandex($this->getHttpClient(), 'street');
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(48.863216489553, 2.388771995902061)->withLocale('en-US'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var YandexAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(48.87132, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.404017, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(48.86294, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(2.387497, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(48.877038, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(2.406587, $result->getBounds()->getEast(), 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('20e Arrondissement', $result->getSubLocality());
        $this->assertEquals('Paris', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Île-de-France', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());
        $this->assertEquals('street', $result->getPrecision());
        $this->assertEquals('Avenue Gambetta', $result->getName());
        $this->assertEquals('street', $result->getKind());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());

        /** @var YandexAddress $result */
        $result = $results->get(1);
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(48.863230, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.388261, $result->getCoordinates()->getLongitude(), 0.01);

        /** @var YandexAddress $result */
        $result = $results->get(2);
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(48.866022, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.389662, $result->getCoordinates()->getLongitude(), 0.01);

        /** @var YandexAddress $result */
        $result = $results->get(3);
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(48.863918, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.387767, $result->getCoordinates()->getLongitude(), 0.01);

        /** @var YandexAddress $result */
        $result = $results->get(4);
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(48.863787, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(2.389600, $result->getCoordinates()->getLongitude(), 0.01);
    }

    public function testReverseWithRealCoordinatesWithUALocaleAndHouseToponym(): void
    {
        $provider = new Yandex($this->getHttpClient(), 'house');
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(60.4539471768582, 22.2567842183875)->withLocale('uk-UA'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var YandexAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(60.454462, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(22.256561, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(60.45345, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(22.254513, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(60.455474, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(22.258609, $result->getBounds()->getEast(), 0.01);
        $this->assertEquals(35, $result->getStreetNumber());
        $this->assertEquals('Läntinen Pitkäkatu', $result->getStreetName());
        $this->assertNull($result->getLocality());
        $this->assertEquals('город Турку', $result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Исконная Финляндия', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Фінляндія', $result->getCountry()->getName());
        $this->assertEquals('FI', $result->getCountry()->getCode());
        $this->assertEquals('exact', $result->getPrecision());
        $this->assertEquals('house', $result->getKind());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testReverseWithRealCoordinatesWithTRLocaleAndLocalityToponym(): void
    {
        $provider = new Yandex($this->getHttpClient(), 'locality');
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(40.900640, 29.198184)->withLocale('tr-TR'));

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(5, $results);

        /** @var YandexAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEqualsWithDelta(41.01117, $result->getCoordinates()->getLatitude(), 0.01);
        $this->assertEqualsWithDelta(28.978151, $result->getCoordinates()->getLongitude(), 0.01);
        $this->assertNotNull($result->getBounds());
        $this->assertEqualsWithDelta(40.795964, $result->getBounds()->getSouth(), 0.01);
        $this->assertEqualsWithDelta(28.401361, $result->getBounds()->getWest(), 0.01);
        $this->assertEqualsWithDelta(41.224206, $result->getBounds()->getNorth(), 0.01);
        $this->assertEqualsWithDelta(29.420984, $result->getBounds()->getEast(), 0.01);
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('İstanbul', $result->getLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('İstanbul', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('Türkiye', $result->getCountry()->getName());
        $this->assertEquals('TR', $result->getCountry()->getCode());
        $this->assertEquals('other', $result->getPrecision());
        $this->assertEquals('İstanbul', $result->getName());
        $this->assertEquals('locality', $result->getKind());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testReverseMetroStationToGetName(): void
    {
        $provider = new Yandex($this->getHttpClient(), 'metro');
        $results = $provider->reverseQuery(ReverseQuery::fromCoordinates(60.036843, 30.324285));

        /** @var YandexAddress $result */
        $result = $results->first();
        $this->assertInstanceOf('Geocoder\Provider\Yandex\Model\YandexAddress', $result);
        $this->assertEquals('other', $result->getPrecision());
        $this->assertEquals('метро Озерки', $result->getName());
        $this->assertEquals('metro', $result->getKind());
    }
}
