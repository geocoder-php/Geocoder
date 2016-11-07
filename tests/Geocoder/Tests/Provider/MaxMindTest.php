<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Location;
use Geocoder\Tests\TestCase;
use Geocoder\Provider\MaxMind;

class MaxMindTest extends TestCase
{
    public function testGetName()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('maxmind', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     */
    public function testGeocodeWithNullApiKey()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), null);
        $provider->geocode('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MaxMind provider does not support street addresses, only IP addresses.
     */
    public function testGeocodeWithNull()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MaxMind provider does not support street addresses, only IP addresses.
     */
    public function testGeocodeWithEmpty()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MaxMind provider does not support street addresses, only IP addresses.
     */
    public function testGeocodeWithAddress()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), 'api_key');
        $provider->geocode('10 avenue Gambetta, Paris, France');
    }

    public function testGeocodeWithLocalhostIPv4()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), 'api_key');
        $results  = $provider->geocode('127.0.0.1');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    public function testGeocodeWithLocalhostIPv6()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), 'api_key');
        $results  = $provider->geocode('::1');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage Unknown MaxMind service foo
     */
    public function testGeocodeWithRealIPv4AndNotSupportedService()
    {
        $provider = new MaxMind($this->getMockAdapter(), 'api_key', 'foo');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage Unknown MaxMind service 12345
     */
    public function testGeocodeWithRealIPv6AndNotSupportedService()
    {
        $provider = new MaxMind($this->getMockAdapter(), 'api_key', 12345);
        $provider->geocode('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://geoip.maxmind.com/f?l=api_key&i=74.200.247.59".
     */
    public function testGeocodeWithRealIPv4GetsNullContent()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(null), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not execute query "http://geoip.maxmind.com/f?l=api_key&i=74.200.247.59".
     */
    public function testGeocodeWithRealIPv4GetsEmptyContent()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(''), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    public function testGeocodeWithRealIPv4GetsFakeContentFormattedEmpty()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,'), 'api_key');
        $results  = $provider->geocode('74.200.247.59');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertNull($result->getCoordinates());

        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertNull($result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertEmpty($result->getAdminLevels());
        $this->assertNull($result->getCountry()->getName());
        $this->assertNull($result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeWithRealIPv4GetsFakeContent()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(
            'US,TX,Plano,75093,33.034698486328,-96.813400268555,,,,'), 'api_key');
        $results  = $provider->geocode('74.200.247.59');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(33.034698486328, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(-96.813400268555, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals(75093, $result->getPostalCode());
        $this->assertEquals('Plano', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertNull($result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('TX', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());

        $provider2 = new MaxMind($this->getMockAdapterReturns('FR,,,,,,,,,'), 'api_key');
        $result2   = $provider2->geocode('74.200.247.59');
        $this->assertEquals('France', $result2->first()->getCountry()->getName());

        $provider3 = new MaxMind($this->getMockAdapterReturns('GB,,,,,,,,,'), 'api_key');
        $result3   = $provider3->geocode('74.200.247.59');
        $this->assertEquals('United Kingdom', $result3->first()->getCountry()->getName());

        $provider4 = new MaxMind($this->getMockAdapterReturns(
            'US,CA,San Francisco,94110,37.748402,-122.415604,807,415,"Layered Technologies","Automattic"'), 'api_key');
        $results   = $provider4->geocode('74.200.247.59');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(37.748402, $result->getCoordinates()->getLatitude(), '', 0.0001);
        $this->assertEquals(-122.415604, $result->getCoordinates()->getLongitude(), '', 0.0001);
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals(94110, $result->getPostalCode());
        $this->assertEquals('San Francisco', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertNull($result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('CA', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGeocodeWithRealIPv4AndInvalidApiKeyGetsFakeContent()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,,INVALID_LICENSE_KEY'), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGeocodeOmniServiceWithRealIPv6AndInvalidApiKeyGetsFakeContent()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,,,,,,,,,,,,,,,,INVALID_LICENSE_KEY'),
            'api_key', MaxMind::OMNI_SERVICE);
        $provider->geocode('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGeocodeWithRealIPv4AndInvalidApiKey()
    {
        $provider = new MaxMind($this->getAdapter(), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGeocodeWithRealIPv4AndInvalidApiKeyGetsFakeContent2()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,,LICENSE_REQUIRED'), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentials
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGeocodeOmniServiceWithRealIPv6AndInvalidApiKeyGetsFakeContent2()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,,,,,,,,,,,,,,,INVALID_LICENSE_KEY'),
            'api_key', MaxMind::OMNI_SERVICE);
        $provider->geocode('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not retrieve information for the supplied IP address.
     */
    public function testGeocodeWithRealIPv4GetsFakeContentWithIpNotFound()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,,IP_NOT_FOUND'), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Could not retrieve information for the supplied IP address.
     */
    public function testGeocodeOmniServiceWithRealIPv6GetsFakeContentWithIpNotFound()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,,,,,,,,,,,,,,,IP_NOT_FOUND'),
            'api_key', MaxMind::OMNI_SERVICE);
        $provider->geocode('::fff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage Invalid result returned by the API.
     */
    public function testGeocodeGetsFakeContentWithInvalidData()
    {
        $provider = new MaxMind($this->getMockAdapterReturns(',,,,,,,,,,'), 'api_key');
        $provider->geocode('74.200.247.59');
    }

    public function testGeocodeServiceWithRealIPv4()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMind($this->getAdapter($_SERVER['MAXMIND_API_KEY']), $_SERVER['MAXMIND_API_KEY']);
        $results  = $provider->geocode('74.200.247.159');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(33.034698, $result->getCoordinates()->getLatitude(), '', 0.1);
        $this->assertEquals(-96.813400, $result->getCoordinates()->getLongitude(), '', 0.1);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals(75093, $result->getPostalCode());
        $this->assertEquals('Plano', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertNull($result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('TX', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeOmniServiceWithRealIPv4()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMind($this->getAdapter($_SERVER['MAXMIND_API_KEY']), $_SERVER['MAXMIND_API_KEY'],
            MaxMind::OMNI_SERVICE);
        $results  = $provider->geocode('74.200.247.159');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(33.0347, $result->getCoordinates()->getLatitude(), '', 0.1);
        $this->assertEquals(-96.8134, $result->getCoordinates()->getLongitude(), '', 0.1);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals(75093, $result->getPostalCode());
        $this->assertEquals('Plano', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Texas', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('TX', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('America/Chicago', $result->getTimezone());
    }

    public function testGeocodeOmniServiceWithRealIPv4WithSslAndEncoding()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMind($this->getAdapter($_SERVER['MAXMIND_API_KEY']), $_SERVER['MAXMIND_API_KEY'],
            MaxMind::OMNI_SERVICE, true);
        $results  = $provider->geocode('189.26.128.80');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(-27.5833, $result->getCoordinates()->getLatitude(), '', 0.1);
        $this->assertEquals(-48.5666, $result->getCoordinates()->getLongitude(), '', 0.1);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Florianópolis', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Santa Catarina', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('26', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Brazil', $result->getCountry()->getName());
        $this->assertEquals('BR', $result->getCountry()->getCode());
        $this->assertEquals('America/Sao_Paulo', $result->getTimezone());
    }

    public function testGeocodeWithRealIPv6()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMind($this->getAdapter($_SERVER['MAXMIND_API_KEY']), $_SERVER['MAXMIND_API_KEY']);
        $results  = $provider->geocode('2002:4293:f4d6:0:0:0:0:0');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(40.2181, $result->getCoordinates()->getLatitude(), '', 0.1);
        $this->assertEquals(-111.6133, $result->getCoordinates()->getLongitude(), '', 0.1);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals(84606, $result->getPostalCode());
        $this->assertEquals('Provo', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertNull($result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('UT', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testGeocodeOmniServiceWithRealIPv6WithSsl()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMind($this->getAdapter($_SERVER['MAXMIND_API_KEY']), $_SERVER['MAXMIND_API_KEY'],
            MaxMind::OMNI_SERVICE, true);
        $results  = $provider->geocode('2002:4293:f4d6:0:0:0:0:0');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals(40.2181, $result->getCoordinates()->getLatitude(), '', 0.1);
        $this->assertEquals(-111.6133, $result->getCoordinates()->getLongitude(), '', 0.1);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertEquals(84606, $result->getPostalCode());
        $this->assertEquals('Provo', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('Utah', $result->getAdminLevels()->get(1)->getName());
        $this->assertEquals('UT', $result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertEquals('America/Denver', $result->getTimezone());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MaxMind provider is not able to do reverse geocoding.
     */
    public function testReverse()
    {
        $provider = new MaxMind($this->getMockAdapter($this->never()), 'api_key');
        $provider->reverse(1, 2);
    }
}
