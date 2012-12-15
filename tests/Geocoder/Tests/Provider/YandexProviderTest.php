<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\YandexProvider;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class YandexProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new YandexProvider($this->getMockAdapter($this->never()));
        $this->assertEquals('yandex', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The YandexProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new YandexProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The YandexProvider does not support IP addresses.
     */
    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new YandexProvider($this->getMockAdapter($this->never()));
        $provider->getGeocodedData('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?results=1&format=json&geocode=
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new YandexProvider($this->getMockAdapter());
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?results=1&format=json&geocode=
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new YandexProvider($this->getMockAdapter());
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?results=1&format=json&geocode=foobar
     */
    public function testGetGeocodedDataWithInvalidData()
    {
        $provider = new YandexProvider($this->getMockAdapter());
        $provider->getGeocodedData('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?results=1&format=json&geocode=Kabasakal+Caddesi%2C+Istanbul%2C+Turkey
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new YandexProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('Kabasakal Caddesi, Istanbul, Turkey');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?results=1&format=json&geocode=foobar
     */
    public function testGetGeocodedDataWithFakeAddress()
    {
        $provider = new YandexProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $provider->getGeocodedData('foobar');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $provider = new YandexProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result   = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertEquals(48.863277, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.389016, $result['longitude'], '', 0.0001);
        $this->assertEquals(48.861926, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(2.386967, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(48.864629, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(2.391064, $result['bounds']['east'], '', 0.0001);
        $this->assertEquals(10, $result['streetNumber']);
        $this->assertEquals('Иль-Де-Франс', $result['cityDistrict']);
        $this->assertEquals('Avenue Gambetta', $result['streetName']);
        $this->assertEquals('Франция', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);

        // not provided
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressWithUALocale()
    {
        $provider = new YandexProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'uk-UA');
        $result   = $provider->getGeocodedData('Tagensvej 47, Copenhagen, Denmark');

        $this->assertEquals(55.675682, $result['latitude'], '', 0.01);
        $this->assertEquals(12.567602, $result['longitude'], '', 0.01);
        $this->assertEquals(55.614999, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(12.45295, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(55.73259, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(12.65075, $result['bounds']['east'], '', 0.01);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Капитал', $result['cityDistrict']);
        $this->assertNull($result['streetName']);
        $this->assertEquals('Данія', $result['country']);
        $this->assertEquals('DK', $result['countryCode']);

        // not provided
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressWithUSLocale()
    {
        $provider = new YandexProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'en-US');
        $result   = $provider->getGeocodedData('1600 Pennsylvania Ave, Washington');

        $this->assertEquals(39.664402, $result['latitude'], '', 0.0001);
        $this->assertEquals(-77.568609, $result['longitude'], '', 0.0001);
        $this->assertEquals(39.655911, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(-77.570989, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(39.672421, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(-77.568483, $result['bounds']['east'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Maryland', $result['cityDistrict']);
        $this->assertNull($result['streetName']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);

        // not provided
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealAddressWithBYLocale()
    {
        $provider = new YandexProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'be-BY');
        $result   = $provider->getGeocodedData('ул.Ленина, 19, Минск 220030, Республика Беларусь');

        $this->assertEquals(53.898077, $result['latitude'], '', 0.0001);
        $this->assertEquals(27.563673, $result['longitude'], '', 0.0001);
        $this->assertEquals(53.896867, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(27.561624, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(53.899286, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(27.565721, $result['bounds']['east'], '', 0.0001);
        $this->assertEquals(19, $result['streetNumber']);
        $this->assertNull($result['cityDistrict']);
        $this->assertEquals('улица Ленина', $result['streetName']);
        $this->assertEquals('Беларусь', $result['country']);
        $this->assertEquals('BY', $result['countryCode']);

        // not provided
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?results=1&format=json&geocode=2.000000,1.000000
     */
    public function testGetReversedData()
    {
        $provider = new YandexProvider($this->getMockAdapter());
        $provider->getReversedData(array(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?results=1&format=json&geocode=0.000000,0.000000
     */
    public function testGetReversedDataWithInvalidData()
    {
        $provider = new YandexProvider($this->getMockAdapter());
        $provider->getReversedData(array('foo', 'bar'));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?results=1&format=json&geocode=2.388772,48.863216
     */
    public function testGetReversedDataWithAddressGetsNullContent()
    {
        $provider = new YandexProvider($this->getMockAdapterReturns(null));
        $provider->getReversedData(array(48.863216489553, 2.388771995902061));
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        $provider = new YandexProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter());
        $result = $provider->getReversedData(array(48.863216489553, 2.388771995902061));

        $this->assertEquals(48.863212, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.388773, $result['longitude'], '', 0.0001);
        $this->assertEquals(48.86294, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(2.387497, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(48.877038, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(2.423214, $result['bounds']['east'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Иль-Де-Франс', $result['cityDistrict']);
        $this->assertEquals('Avenue Gambetta', $result['streetName']);
        $this->assertEquals('Франция', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);

        // not provided
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetReversedDataWithRealCoordinatesWithUSLocaleAndStreeToponym()
    {
        $provider = new YandexProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'en-US', 'street');
        $result = $provider->getReversedData(array(48.863216489553, 2.388771995902061));

        $this->assertEquals(48.87132, $result['latitude'], '', 0.0001);
        $this->assertEquals(2.404017, $result['longitude'], '', 0.0001);
        $this->assertEquals(48.86294, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(2.387497, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(48.877038, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(2.423214, $result['bounds']['east'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('Ile-de-France', $result['cityDistrict']);
        $this->assertEquals('Avenue Gambetta', $result['streetName']);
        $this->assertEquals('France', $result['country']);
        $this->assertEquals('FR', $result['countryCode']);

        // not provided
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetReversedDataWithRealCoordinatesWithUALocaleAndHouseToponym()
    {
        $provider = new YandexProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'uk-UA', 'house');
        $result = $provider->getReversedData(array(60.4539471768582, 22.2567842183875));

        $this->assertEquals(60.454462, $result['latitude'], '', 0.0001);
        $this->assertEquals(22.256561, $result['longitude'], '', 0.0001);
        $this->assertEquals(60.45345, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(22.254513, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(60.455474, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(22.258609, $result['bounds']['east'], '', 0.0001);
        $this->assertEquals(36, $result['streetNumber']);
        $this->assertEquals('Исконная Финляндия', $result['cityDistrict']);
        //$this->assertEquals('Bangårdsgatan', $result['streetName']);
        $this->assertEquals('Фінляндія', $result['country']);
        $this->assertEquals('FI', $result['countryCode']);

        // not provided
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['timezone']);
    }

    public function testGetReversedDataWithRealCoordinatesWithTRLocaleAndLocalityToponym()
    {
        $provider = new YandexProvider(new \Geocoder\HttpAdapter\CurlHttpAdapter(), 'tr-TR', 'locality');
        $result = $provider->getReversedData(array(40.900640, 29.198184));

        $this->assertEquals(40.909452, $result['latitude'], '', 0.0001);
        $this->assertEquals(29.052244, $result['longitude'], '', 0.0001);
        $this->assertEquals(40.903932, $result['bounds']['south'], '', 0.0001);
        $this->assertEquals(29.041446, $result['bounds']['west'], '', 0.0001);
        $this->assertEquals(40.913759, $result['bounds']['north'], '', 0.0001);
        $this->assertEquals(29.056834, $result['bounds']['east'], '', 0.0001);
        $this->assertNull($result['streetNumber']);
        $this->assertEquals('İstanbul', $result['cityDistrict']);
        $this->assertNull($result['streetName']);
        $this->assertEquals('Türkiye', $result['country']);
        $this->assertEquals('TR', $result['countryCode']);

        // not provided
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['region']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['timezone']);
    }
}
