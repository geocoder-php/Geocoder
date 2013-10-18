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
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?format=json&geocode=&results=5
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new YandexProvider($this->getMockAdapter());
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?format=json&geocode=&results=5
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new YandexProvider($this->getMockAdapter());
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?format=json&geocode=foobar&results=5
     */
    public function testGetGeocodedDataWithInvalidData()
    {
        $provider = new YandexProvider($this->getMockAdapter());
        $provider->getGeocodedData('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?format=json&geocode=Kabasakal+Caddesi%2C+Istanbul%2C+Turkey&results=5
     */
    public function testGetGeocodedDataWithAddressGetsNullContent()
    {
        $provider = new YandexProvider($this->getMockAdapterReturns(null));
        $provider->getGeocodedData('Kabasakal Caddesi, Istanbul, Turkey');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?format=json&geocode=foobar&results=5
     */
    public function testGetGeocodedDataWithFakeAddress()
    {
        $provider = new YandexProvider($this->getAdapter());
        $provider->getGeocodedData('foobar');
    }

    public function testGetGeocodedDataWithRealAddress()
    {
        $provider = new YandexProvider($this->getAdapter());
        $results  = $provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(48.863277, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(2.389016, $results[0]['longitude'], '', 0.01);
        $this->assertEquals(48.861926, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.386967, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(48.864629, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.391064, $results[0]['bounds']['east'], '', 0.01);
        $this->assertEquals(10, $results[0]['streetNumber']);
        $this->assertEquals('Иль-Де-Франс', $results[0]['region']);
        $this->assertEquals('Avenue Gambetta', $results[0]['streetName']);
        $this->assertEquals('Франция', $results[0]['country']);
        $this->assertEquals('FR', $results[0]['countryCode']);

        // not provided
        $this->assertNull($results[0]['zipcode']);
        $this->assertNull($results[0]['city']);
        $this->assertNull($results[0]['cityDistrict']);
        $this->assertNull($results[0]['regionCode']);
        $this->assertNull($results[0]['timezone']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(48.810138, $results[1]['latitude'], '', 0.01);
        $this->assertEquals(2.435926, $results[1]['longitude'], '', 0.01);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(48.892773, $results[2]['latitude'], '', 0.01);
        $this->assertEquals(2.246174, $results[2]['longitude'], '', 0.01);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(48.844640, $results[3]['latitude'], '', 0.01);
        $this->assertEquals(2.420493, $results[3]['longitude'], '', 0.01);

        $this->assertInternalType('array', $results[4]);
        $this->assertEquals(48.813520, $results[4]['latitude'], '', 0.01);
        $this->assertEquals(2.324642, $results[4]['longitude'], '', 0.01);
    }

    public function testGetGeocodedDataWithRealAddressWithUALocale()
    {
        $provider = new YandexProvider($this->getAdapter(), 'uk-UA');
        $results  = $provider->getGeocodedData('Copenhagen, Denmark');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(55.675682, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(12.567602, $results[0]['longitude'], '', 0.01);
        $this->assertEquals(55.614999, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(12.45295, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(55.73259, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(12.65075, $results[0]['bounds']['east'], '', 0.01);
        $this->assertNull($results[0]['streetNumber']);
        $this->assertEquals('Столичная область', $results[0]['region']);
        $this->assertNull($results[0]['streetName']);
        $this->assertEquals('Копенгаген', $results[0]['city']);
        $this->assertEquals('Данія', $results[0]['country']);
        $this->assertEquals('DK', $results[0]['countryCode']);

        // not provided
        $this->assertNull($results[0]['zipcode']);
        $this->assertNull($results[0]['cityDistrict']);
        $this->assertNull($results[0]['regionCode']);
        $this->assertNull($results[0]['timezone']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(55.614439, $results[1]['latitude'], '', 0.01);
        $this->assertEquals(12.645351, $results[1]['longitude'], '', 0.01);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(55.713258, $results[2]['latitude'], '', 0.01);
        $this->assertEquals(12.534930, $results[2]['longitude'], '', 0.01);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(55.698878, $results[3]['latitude'], '', 0.01);
        $this->assertEquals(12.578211, $results[3]['longitude'], '', 0.01);

        $this->assertInternalType('array', $results[4]);
        $this->assertEquals(55.690380, $results[4]['latitude'], '', 0.01);
        $this->assertEquals(12.554827, $results[4]['longitude'], '', 0.01);
    }

    public function testGetGeocodedDataWithRealAddressWithUSLocale()
    {
        $provider = new YandexProvider($this->getAdapter(), 'en-US');
        $results  = $provider->getGeocodedData('1600 Pennsylvania Ave, Washington');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(38.898720, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(-77.036384, $results[0]['longitude'], '', 0.01);
        $this->assertEquals(38.897119, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(-77.058078, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(38.90032, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(-77.012453, $results[0]['bounds']['east'], '', 0.01);
        $this->assertEquals('District of Columbia', $results[0]['region']);
        $this->assertEquals('Pennsylvania Ave NW', $results[0]['streetName']);
        $this->assertEquals('Washington', $results[0]['city']);
        $this->assertEquals('United States', $results[0]['country']);
        $this->assertEquals('US', $results[0]['countryCode']);

        // not provided
        $this->assertNull($results[0]['zipcode']);
        $this->assertNull($results[0]['cityDistrict']);
        $this->assertNull($results[0]['regionCode']);
        $this->assertNull($results[0]['timezone']);

        $this->assertInternalType('array', $results[1]);
        $this->assertInternalType('array', $results[2]);
        $this->assertInternalType('array', $results[3]);
        $this->assertInternalType('array', $results[4]);
    }

    public function testGetGeocodedDataWithRealAddressWithBYLocale()
    {
        $provider = new YandexProvider($this->getAdapter(), 'be-BY');
        $result   = $provider->getGeocodedData('ул.Ленина, 19, Минск 220030, Республика Беларусь');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(53.898077, $result['latitude'], '', 0.01);
        $this->assertEquals(27.563673, $result['longitude'], '', 0.01);
        $this->assertEquals(53.896867, $result['bounds']['south'], '', 0.01);
        $this->assertEquals(27.561624, $result['bounds']['west'], '', 0.01);
        $this->assertEquals(53.899286, $result['bounds']['north'], '', 0.01);
        $this->assertEquals(27.565721, $result['bounds']['east'], '', 0.01);
        $this->assertEquals(19, $result['streetNumber']);
        $this->assertNull($result['region']);
        $this->assertEquals('улица Ленина', $result['streetName']);
        $this->assertEquals('Минск', $result['city']);
        $this->assertEquals('Беларусь', $result['country']);
        $this->assertEquals('BY', $result['countryCode']);

        // not provided
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?format=json&geocode=2.000000,1.000000&results=5
     */
    public function testGetReversedData()
    {
        $provider = new YandexProvider($this->getMockAdapter());
        $provider->getReversedData(array(1, 2));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?format=json&geocode=0.000000,0.000000&results=5
     */
    public function testGetReversedDataWithInvalidData()
    {
        $provider = new YandexProvider($this->getMockAdapter());
        $provider->getReversedData(array('foo', 'bar'));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geocode-maps.yandex.ru/1.x/?format=json&geocode=2.388772,48.863216&results=5
     */
    public function testGetReversedDataWithAddressGetsNullContent()
    {
        $provider = new YandexProvider($this->getMockAdapterReturns(null));
        $provider->getReversedData(array(48.863216489553, 2.388771995902061));
    }

    public function testGetReversedDataWithRealCoordinates()
    {
        $provider = new YandexProvider($this->getAdapter());
        $results  = $provider->getReversedData(array(48.863216489553, 2.388771995902061));

        $this->assertInternalType('array', $results);
        $this->assertCount(3, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(48.863212, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(2.388773, $results[0]['longitude'], '', 0.01);
        $this->assertEquals(48.86294, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.387497, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(48.877038, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.423214, $results[0]['bounds']['east'], '', 0.01);
        $this->assertNull($results[0]['streetNumber']);
        $this->assertEquals('Иль-Де-Франс', $results[0]['region']);
        $this->assertEquals('Avenue Gambetta', $results[0]['streetName']);
        $this->assertEquals('Франция', $results[0]['country']);
        $this->assertEquals('FR', $results[0]['countryCode']);

        // not provided
        $this->assertNull($results[0]['zipcode']);
        $this->assertNull($results[0]['city']);
        $this->assertNull($results[0]['cityDistrict']);
        $this->assertNull($results[0]['regionCode']);
        $this->assertNull($results[0]['timezone']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(48.709273, $results[1]['latitude'], '', 0.01);
        $this->assertEquals(2.503371, $results[1]['longitude'], '', 0.01);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(46.621810, $results[2]['latitude'], '', 0.01);
        $this->assertEquals(2.452113, $results[2]['longitude'], '', 0.01);
    }

    public function testGetReversedDataWithRealCoordinatesWithUSLocaleAndStreeToponym()
    {
        $provider = new YandexProvider($this->getAdapter(), 'en-US', 'street');
        $results  = $provider->getReversedData(array(48.863216489553, 2.388771995902061));

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(48.87132, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(2.404017, $results[0]['longitude'], '', 0.01);
        $this->assertEquals(48.86294, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(2.387497, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(48.877038, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(2.423214, $results[0]['bounds']['east'], '', 0.01);
        $this->assertNull($results[0]['streetNumber']);
        $this->assertEquals('Ile-de-France', $results[0]['region']);
        $this->assertEquals('Avenue Gambetta', $results[0]['streetName']);
        $this->assertEquals('France', $results[0]['country']);
        $this->assertEquals('FR', $results[0]['countryCode']);

        // not provided
        $this->assertNull($results[0]['zipcode']);
        $this->assertNull($results[0]['city']);
        $this->assertNull($results[0]['cityDistrict']);
        $this->assertNull($results[0]['regionCode']);
        $this->assertNull($results[0]['timezone']);

        $this->assertInternalType('array', $results[1]);
        $this->assertEquals(48.863230, $results[1]['latitude'], '', 0.01);
        $this->assertEquals(2.388261, $results[1]['longitude'], '', 0.01);

        $this->assertInternalType('array', $results[2]);
        $this->assertEquals(48.866022, $results[2]['latitude'], '', 0.01);
        $this->assertEquals(2.389662, $results[2]['longitude'], '', 0.01);

        $this->assertInternalType('array', $results[3]);
        $this->assertEquals(48.863918, $results[3]['latitude'], '', 0.01);
        $this->assertEquals(2.387767, $results[3]['longitude'], '', 0.01);

        $this->assertInternalType('array', $results[4]);
        $this->assertEquals(48.863787, $results[4]['latitude'], '', 0.01);
        $this->assertEquals(2.389600, $results[4]['longitude'], '', 0.01);
    }

    public function testGetReversedDataWithRealCoordinatesWithUALocaleAndHouseToponym()
    {
        $provider = new YandexProvider($this->getAdapter(), 'uk-UA', 'house');
        $results  = $provider->getReversedData(array(60.4539471768582, 22.2567842183875));

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(60.454462, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(22.256561, $results[0]['longitude'], '', 0.01);
        $this->assertEquals(60.45345, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(22.254513, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(60.455474, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(22.258609, $results[0]['bounds']['east'], '', 0.01);
        $this->assertEquals(36, $results[0]['streetNumber']);
        $this->assertEquals('Исконная Финляндия', $results[0]['region']);
        //$this->assertEquals('Bangårdsgatan', $results[0]['streetName']);
        $this->assertEquals('Турку', $results[0]['city']);
        $this->assertEquals('Фінляндія', $results[0]['country']);
        $this->assertEquals('FI', $results[0]['countryCode']);

        // not provided
        $this->assertNull($results[0]['zipcode']);
        $this->assertNull($results[0]['cityDistrict']);
        $this->assertNull($results[0]['regionCode']);
        $this->assertNull($results[0]['timezone']);
    }

    public function testGetReversedDataWithRealCoordinatesWithTRLocaleAndLocalityToponym()
    {
        $provider = new YandexProvider($this->getAdapter(), 'tr-TR', 'locality');
        $results  = $provider->getReversedData(array(40.900640, 29.198184));

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        $this->assertInternalType('array', $results[0]);
        $this->assertEquals(40.909452, $results[0]['latitude'], '', 0.01);
        $this->assertEquals(29.138608, $results[0]['longitude'], '', 0.01);
        $this->assertEquals(40.860413, $results[0]['bounds']['south'], '', 0.01);
        $this->assertEquals(29.072708, $results[0]['bounds']['west'], '', 0.01);
        $this->assertEquals(40.960403, $results[0]['bounds']['north'], '', 0.01);
        $this->assertEquals(29.204508, $results[0]['bounds']['east'], '', 0.01);
        $this->assertNull($results[0]['streetNumber']);
        $this->assertEquals('İstanbul', $results[0]['region']);
        $this->assertNull($results[0]['streetName']);
        $this->assertEquals('Dragos', $results[0]['city']);
        $this->assertEquals('Türkiye', $results[0]['country']);
        $this->assertEquals('TR', $results[0]['countryCode']);

        // not provided
        $this->assertNull($results[0]['zipcode']);
        $this->assertNull($results[0]['cityDistrict']);
        $this->assertNull($results[0]['regionCode']);
        $this->assertNull($results[0]['timezone']);

        $this->assertInternalType('array', $results[1]);
        $this->assertInternalType('array', $results[2]);
        $this->assertInternalType('array', $results[3]);
        $this->assertInternalType('array', $results[4]);
    }
}
