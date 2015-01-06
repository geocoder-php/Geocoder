<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\MaxMindProvider;

class MaxMindProviderTest extends TestCase
{
    public function testGetName()
    {
        $provider = new MaxMindProvider($this->getMockAdapter($this->never()), 'api_key');
        $this->assertEquals('maxmind', $provider->getName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new MaxMindProvider($this->getMock('Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MaxMindProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithNull()
    {
        $provider = new MaxMindProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData(null);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MaxMindProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new MaxMindProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MaxMindProvider does not support street addresses.
     */
    public function testGetGeocodedDataWithAddress()
    {
        $provider = new MaxMindProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getGeocodedData('10 avenue Gambetta, Paris, France');
    }

    public function testGetGeocodedDataWithLocalhostIPv4()
    {
        $provider = new MaxMindProvider($this->getMockAdapter($this->never()), 'api_key');
        $result   = $provider->getGeocodedData('127.0.0.1');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('zipcode', $result);
        $this->assertArrayNotHasKey('timezone', $result);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['county']);
        $this->assertEquals('localhost', $result['country']);
    }

    public function testGetGeocodedDataWithLocalhostIPv6()
    {
        $provider = new MaxMindProvider($this->getMockAdapter($this->never()), 'api_key');
        $result   = $provider->getGeocodedData('::1');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('zipcode', $result);
        $this->assertArrayNotHasKey('timezone', $result);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['county']);
        $this->assertEquals('localhost', $result['country']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage Unknown MaxMind service foo
     */
    public function testGetGeocodedDataWithRealIPv4AndNotSupportedService()
    {
        $provider = new MaxMindProvider($this->getMock('Geocoder\HttpAdapter\HttpAdapterInterface'), 'api_key', 'foo');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage Unknown MaxMind service 12345
     */
    public function testGetGeocodedDataWithRealIPv6AndNotSupportedService()
    {
        $provider = new MaxMindProvider($this->getMock('Geocoder\HttpAdapter\HttpAdapterInterface'), 'api_key', 12345);
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geoip.maxmind.com/f?l=api_key&i=74.200.247.59
     */
    public function testGetGeocodedDataWithRealIPv4GetsNullContent()
    {
        $provider = new MaxMindProvider($this->getMockAdapterReturns(null), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not execute query http://geoip.maxmind.com/f?l=api_key&i=74.200.247.59
     */
    public function testGetGeocodedDataWithRealIPv4GetsEmptyContent()
    {
        $provider = new MaxMindProvider($this->getMockAdapterReturns(''), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedDataWithRealIPv4GetsFakeContentFormattedEmpty()
    {
        $provider = new MaxMindProvider($this->getMockAdapterReturns(',,,,,,,,,'), 'api_key');
        $result   = $provider->getGeocodedData('74.200.247.59');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertNull($result['country']);
        $this->assertNull($result['countryCode']);
        $this->assertNull($result['regionCode']);
        $this->assertNull($result['city']);
        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataWithRealIPv4GetsFakeContent()
    {
        $provider = new MaxMindProvider($this->getMockAdapterReturns(
            'US,TX,Plano,75093,33.034698486328,-96.813400268555,,,,'), 'api_key');
        $result   = $provider->getGeocodedData('74.200.247.59');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertEquals('TX', $result['regionCode']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals(33.034698486328, $result['latitude'], '', 0.0001);
        $this->assertEquals(-96.813400268555, $result['longitude'], '', 0.0001);
        $this->assertNull($result['timezone']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['timezone']);

        $provider2 = new MaxMindProvider($this->getMockAdapterReturns('FR,,,,,,,,,'), 'api_key');
        $result2   = $provider2->getGeocodedData('74.200.247.59');
        $this->assertEquals('France', $result2[0]['country']);

        $provider3 = new MaxMindProvider($this->getMockAdapterReturns('GB,,,,,,,,,'), 'api_key');
        $result3   = $provider3->getGeocodedData('74.200.247.59');
        $this->assertEquals('United Kingdom', $result3[0]['country']);

        $provider4 = new MaxMindProvider($this->getMockAdapterReturns(
            'US,CA,San Francisco,94110,37.748402,-122.415604,807,415,"Layered Technologies","Automattic"'), 'api_key');
        $result    = $provider4->getGeocodedData('74.200.247.59');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertEquals('CA', $result['regionCode']);
        $this->assertEquals('San Francisco', $result['city']);
        $this->assertEquals(94110, $result['zipcode']);
        $this->assertEquals(37.748402, $result['latitude'], '', 0.0001);
        $this->assertEquals(-122.415604, $result['longitude'], '', 0.0001);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedDataWithRealIPv4AndInvalidApiKeyGetsFakeContent()
    {
        $provider = new MaxMindProvider($this->getMockAdapterReturns(',,,,,,,,,,INVALID_LICENSE_KEY'), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedOmniServiceDataWithRealIPv6AndInvalidApiKeyGetsFakeContent()
    {
        $provider = new MaxMindProvider($this->getMockAdapterReturns(',,,,,,,,,,,,,,,,,,,,,,,,INVALID_LICENSE_KEY'),
            'api_key', MaxMindProvider::OMNI_SERVICE);
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedDataWithRealIPv4AndInvalidApiKey()
    {
        $provider = new MaxMindProvider($this->getAdapter(), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedDataWithRealIPv4AndInvalidApiKeyGetsFakeContent2()
    {
        $provider = new MaxMindProvider($this->getMockAdapterReturns(',,,,,,,,,,LICENSE_REQUIRED'), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     * @expectedExceptionMessage API Key provided is not valid.
     */
    public function testGetGeocodedOmniServiceDataWithRealIPv6AndInvalidApiKeyGetsFakeContent2()
    {
        $provider = new MaxMindProvider($this->getMockAdapterReturns(',,,,,,,,,,,,,,,,,,,,,,,INVALID_LICENSE_KEY'),
            'api_key', MaxMindProvider::OMNI_SERVICE);
        $provider->getGeocodedData('::ffff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not retrieve informations for the ip address provided.
     */
    public function testGetGeocodedDataWithRealIPv4GetsFakeContentWithIpNotFound()
    {
        $provider = new MaxMindProvider($this->getMockAdapterReturns(',,,,,,,,,,IP_NOT_FOUND'), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Could not retrieve informations for the ip address provided.
     */
    public function testGetGeocodedOmniServiceDataWithRealIPv6GetsFakeContentWithIpNotFound()
    {
        $provider = new MaxMindProvider($this->getMockAdapterReturns(',,,,,,,,,,,,,,,,,,,,,,,IP_NOT_FOUND'),
            'api_key', MaxMindProvider::OMNI_SERVICE);
        $provider->getGeocodedData('::fff:74.200.247.59');
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage Invalid result returned by provider.
     */
    public function testGetGeocodedDataGetsFakeContentWithInvalidData()
    {
        $provider = new MaxMindProvider($this->getMockAdapterReturns(',,,,,,,,,,'), 'api_key');
        $provider->getGeocodedData('74.200.247.59');
    }

    public function testGetGeocodedServiceWithRealIPv4()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMindProvider($this->getAdapter(), $_SERVER['MAXMIND_API_KEY']);
        $result   = $provider->getGeocodedData('74.200.247.159');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(33.034698, $result['latitude'], '', 0.1);
        $this->assertEquals(-96.813400, $result['longitude'], '', 0.1);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('TX', $result['regionCode']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataOmniServiceWithRealIPv4()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMindProvider($this->getAdapter(), $_SERVER['MAXMIND_API_KEY'],
            MaxMindProvider::OMNI_SERVICE);
        $result   = $provider->getGeocodedData('74.200.247.159');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(33.0347, $result['latitude'], '', 0.1);
        $this->assertEquals(-96.8134, $result['longitude'], '', 0.1);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('TX', $result['regionCode']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('America/Chicago', $result['timezone']);
    }

    public function testGeocodeOmniServiceWithRealIPv4WithSslAndEncoding()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMindProvider($this->getAdapter(), $_SERVER['MAXMIND_API_KEY'],
            MaxMindProvider::OMNI_SERVICE, true);
        $result   = $provider->getGeocodedData('189.26.128.80');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(-27.5833, $result['latitude'], '', 0.1);
        $this->assertEquals(-48.5666, $result['longitude'], '', 0.1);
        $this->assertEquals('Florianópolis', $result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertEquals('26', $result['regionCode']);
        $this->assertEquals('Brazil', $result['country']);
        $this->assertEquals('BR', $result['countryCode']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertEquals('Santa Catarina', $result['region']);
        $this->assertEquals('America/Sao_Paulo', $result['timezone']);
    }

    public function testGetGeocodedDataWithRealIPv6()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMindProvider($this->getAdapter(), $_SERVER['MAXMIND_API_KEY']);
        $result   = $provider->getGeocodedData('66.147.244.214');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(40.2181, $result['latitude'], '', 0.1);
        $this->assertEquals(-111.6133, $result['longitude'], '', 0.1);
        $this->assertEquals('Provo', $result['city']);
        $this->assertEquals(84606, $result['zipcode']);
        $this->assertEquals('UT', $result['regionCode']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['timezone']);
    }

    public function testGetGeocodedDataOmniServiceWithRealIPv6WithSsl()
    {
        if (!isset($_SERVER['MAXMIND_API_KEY'])) {
            $this->markTestSkipped('You need to configure the MAXMIND_API_KEY value in phpunit.xml');
        }

        $provider = new MaxMindProvider($this->getAdapter(), $_SERVER['MAXMIND_API_KEY'],
            MaxMindProvider::OMNI_SERVICE, true);
        $result   = $provider->getGeocodedData('::ffff:66.147.244.214');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);
        $this->assertEquals(40.2181, $result['latitude'], '', 0.1);
        $this->assertEquals(-111.6133, $result['longitude'], '', 0.1);
        $this->assertEquals('Provo', $result['city']);
        $this->assertEquals(84606, $result['zipcode']);
        $this->assertEquals('UT', $result['regionCode']);
        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('US', $result['countryCode']);
        $this->assertNull($result['bounds']);
        $this->assertNull($result['streetNumber']);
        $this->assertNull($result['streetName']);
        $this->assertNull($result['cityDistrict']);
        $this->assertNull($result['county']);
        $this->assertNull($result['countyCode']);
        $this->assertEquals('Utah', $result['region']);
        $this->assertEquals('America/Denver', $result['timezone']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The MaxMindProvider is not able to do reverse geocoding.
     */
    public function testGetReverseData()
    {
        $provider = new MaxMindProvider($this->getMockAdapter($this->never()), 'api_key');
        $provider->getReversedData(array(1, 2));
    }
}
