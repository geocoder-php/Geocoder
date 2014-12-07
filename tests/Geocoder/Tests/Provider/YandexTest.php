<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\Yandex;

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
        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The Yandex provider does not support IP addresses, only street addresses.
     */
    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new Yandex($this->getMockAdapter($this->never()));
        $provider->geocode('::1');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://geocode-maps.yandex.ru/1.x/?format=json&geocode=&results=5".
     */
    public function testGeocodeWithNull()
    {
        $provider = new Yandex($this->getMockAdapter());
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://geocode-maps.yandex.ru/1.x/?format=json&geocode=&results=5".
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new Yandex($this->getMockAdapter());
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://geocode-maps.yandex.ru/1.x/?format=json&geocode=foobar&results=5".
     */
    public function testGeocodeWithInvalidData()
    {
        $provider = new Yandex($this->getMockAdapter());
        $provider->geocode('foobar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://geocode-maps.yandex.ru/1.x/?format=json&geocode=Kabasakal+Caddesi%2C+Istanbul%2C+Turkey&results=5".
     */
    public function testGeocodeWithAddressGetsNullContent()
    {
        $provider = new Yandex($this->getMockAdapterReturns(null));
        $provider->geocode('Kabasakal Caddesi, Istanbul, Turkey');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://geocode-maps.yandex.ru/1.x/?format=json&geocode=foobar&results=5".
     */
    public function testGeocodeWithFakeAddress()
    {
        $provider = new Yandex($this->getAdapter());
        $provider->geocode('foobar');
    }

    public function testGeocodeWithRealAddress()
    {
        $provider = new Yandex($this->getAdapter());
        $results  = $provider->geocode('10 avenue Gambetta, Paris, France');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.863277, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.389016, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(48.861926, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(2.386967, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(48.864629, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(2.391064, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals(10, $result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('Иль-Де-Франс', $result->getRegion()->getName());
        $this->assertEquals('Франция', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getLocality());
        $this->assertNull($result->getRegion()->getCode());
        $this->assertNull($result->getTimezone());

        /** @var \Geocoder\Model\Address $result */
        $result = $results[1];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.810138, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.435926, $result->getLongitude(), '', 0.01);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[2];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.892773, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.246174, $result->getLongitude(), '', 0.01);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[3];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.844640, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.420493, $result->getLongitude(), '', 0.01);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[4];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.813520, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.324642, $result->getLongitude(), '', 0.01);
    }

    public function testGeocodeWithRealAddressWithUALocale()
    {
        $provider = new Yandex($this->getAdapter(), 'uk-UA');
        $results  = $provider->geocode('Copenhagen, Denmark');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);;

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(55.675682, $result->getLatitude(), '', 0.01);
        $this->assertEquals(12.567602, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(55.614999, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(12.45295, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(55.73259, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(12.65075, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals('Копенгаген', $result->getLocality());
        $this->assertEquals('Столичная область', $result->getRegion()->getName());
        $this->assertEquals('Данія', $result->getCountry()->getName());
        $this->assertEquals('DK', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getRegion()->getCode());
        $this->assertNull($result->getTimezone());

        /** @var \Geocoder\Model\Address $result */
        $result = $results[1];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(55.614439, $result->getLatitude(), '', 0.01);
        $this->assertEquals(12.645351, $result->getLongitude(), '', 0.01);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[2];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(55.713258, $result->getLatitude(), '', 0.01);
        $this->assertEquals(12.534930, $result->getLongitude(), '', 0.01);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[3];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(55.698878, $result->getLatitude(), '', 0.01);
        $this->assertEquals(12.578211, $result->getLongitude(), '', 0.01);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[4];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(55.690380, $result->getLatitude(), '', 0.01);
        $this->assertEquals(12.554827, $result->getLongitude(), '', 0.01);
    }

    public function testGeocodeWithRealAddressWithUSLocale()
    {
        $provider = new Yandex($this->getAdapter(), 'en-US');
        $results  = $provider->geocode('1600 Pennsylvania Ave, Washington');

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(38.898720, $result->getLatitude(), '', 0.01);
        $this->assertEquals(-77.036384, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(38.897119, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(-77.058078, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(38.90032, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(-77.012453, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Pennsylvania Ave NW', $result->getStreetName());
        $this->assertEquals('Washington', $result->getLocality());
        $this->assertEquals('District of Columbia', $result->getRegion()->getName());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getRegion()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealAddressWithBYLocale()
    {
        $provider = new Yandex($this->getAdapter(), 'be-BY');
        $results  = $provider->geocode('ул.Ленина, 19, Минск 220030, Республика Беларусь');

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(53.898077, $result->getLatitude(), '', 0.01);
        $this->assertEquals(27.563673, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(53.896867, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(27.561624, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(53.899286, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(27.565721, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals(19, $result->getStreetNumber());
        $this->assertEquals('улица Ленина', $result->getStreetName());
        $this->assertEquals('Минск', $result->getLocality());
        $this->assertNull($result->getRegion()->getName());
        $this->assertEquals('Беларусь', $result->getCountry()->getName());
        $this->assertEquals('BY', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getRegion()->getCode());
        $this->assertNull($result->getTimezone());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://geocode-maps.yandex.ru/1.x/?format=json&geocode=2.000000,1.000000&results=5".
     */
    public function testReverse()
    {
        $provider = new Yandex($this->getMockAdapter());
        $provider->reverse(1, 2);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://geocode-maps.yandex.ru/1.x/?format=json&geocode=0.000000,0.000000&results=5".
     */
    public function testReverseWithInvalidData()
    {
        $provider = new Yandex($this->getMockAdapter());
        $provider->reverse('foo', 'bar');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://geocode-maps.yandex.ru/1.x/?format=json&geocode=2.388772,48.863216&results=5".
     */
    public function testReverseWithAddressGetsNullContent()
    {
        $provider = new Yandex($this->getMockAdapterReturns(null));
        $provider->reverse(48.863216489553, 2.388771995902061);
    }

    public function testReverseWithRealCoordinates()
    {
        $provider = new Yandex($this->getAdapter());
        $results  = $provider->reverse(48.863216489553, 2.388771995902061);

        $this->assertInternalType('array', $results);
        $this->assertCount(3, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.863212, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.388773, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(48.86294, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(2.387497, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(48.877038, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(2.423214, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('Иль-Де-Франс', $result->getRegion()->getName());
        $this->assertEquals('Франция', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getLocality());
        $this->assertNull($result->getRegion()->getCode());
        $this->assertNull($result->getTimezone());

        /** @var \Geocoder\Model\Address $result */
        $result = $results[1];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.709273, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.503371, $result->getLongitude(), '', 0.01);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[2];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(46.621810, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.452113, $result->getLongitude(), '', 0.01);
    }

    public function testReverseWithRealCoordinatesWithUSLocaleAndStreeToponym()
    {
        $provider = new Yandex($this->getAdapter(), 'en-US', 'street');
        $results  = $provider->reverse(48.863216489553, 2.388771995902061);

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.87132, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.404017, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(48.86294, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(2.387497, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(48.877038, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(2.423214, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Avenue Gambetta', $result->getStreetName());
        $this->assertEquals('Ile-de-France', $result->getRegion()->getName());
        $this->assertEquals('France', $result->getCountry()->getName());
        $this->assertEquals('FR', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getLocality());
        $this->assertNull($result->getRegion()->getCode());
        $this->assertNull($result->getTimezone());

        /** @var \Geocoder\Model\Address $result */
        $result = $results[1];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.863230, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.388261, $result->getLongitude(), '', 0.01);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[2];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.866022, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.389662, $result->getLongitude(), '', 0.01);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[3];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.863918, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.387767, $result->getLongitude(), '', 0.01);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[4];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(48.863787, $result->getLatitude(), '', 0.01);
        $this->assertEquals(2.389600, $result->getLongitude(), '', 0.01);
    }

    public function testReverseWithRealCoordinatesWithUALocaleAndHouseToponym()
    {
        $provider = new Yandex($this->getAdapter(), 'uk-UA', 'house');
        $results  = $provider->reverse(60.4539471768582, 22.2567842183875);

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(60.454462, $result->getLatitude(), '', 0.01);
        $this->assertEquals(22.256561, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(60.45345, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(22.254513, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(60.455474, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(22.258609, $result->getBounds()->getEast(), '', 0.01);
        $this->assertEquals(36, $result->getStreetNumber());
        $this->assertEquals('Ratapihankatu', $result->getStreetName());
        $this->assertEquals('Турку', $result->getLocality());
        $this->assertEquals('Исконная Финляндия', $result->getRegion()->getName());
        $this->assertEquals('Фінляндія', $result->getCountry()->getName());
        $this->assertEquals('FI', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getRegion()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testReverseWithRealCoordinatesWithTRLocaleAndLocalityToponym()
    {
        $provider = new Yandex($this->getAdapter(), 'tr-TR', 'locality');
        $results  = $provider->reverse(40.900640, 29.198184);

        $this->assertInternalType('array', $results);
        $this->assertCount(5, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('Geocoder\Model\Address', $result);
        $this->assertEquals(40.909452, $result->getLatitude(), '', 0.01);
        $this->assertEquals(29.138608, $result->getLongitude(), '', 0.01);
        $this->assertTrue($result->getBounds()->isDefined());
        $this->assertEquals(40.860413, $result->getBounds()->getSouth(), '', 0.01);
        $this->assertEquals(29.072708, $result->getBounds()->getWest(), '', 0.01);
        $this->assertEquals(40.960403, $result->getBounds()->getNorth(), '', 0.01);
        $this->assertEquals(29.204508, $result->getBounds()->getEast(), '', 0.01);
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getStreetNumber());
        $this->assertEquals('Dragos', $result->getLocality());
        $this->assertEquals('İstanbul', $result->getRegion()->getName());
        $this->assertEquals('Türkiye', $result->getCountry()->getName());
        $this->assertEquals('TR', $result->getCountry()->getCode());

        // not provided
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getRegion()->getCode());
        $this->assertNull($result->getTimezone());
    }
}
