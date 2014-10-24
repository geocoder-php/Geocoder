<?php
namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\MaxMindBinary;

class MaxMindBinaryTest extends TestCase
{
    private $binaryFile;

    public function setUp()
    {
        $this->binaryFile = __DIR__ . '/fixtures/GeoLiteCity.dat';
    }

    public static function setUpBeforeClass()
    {
        if (false == function_exists('geoip_open')) {
            throw new \PHPUnit_Framework_SkippedTestError('The maxmind\'s official lib required to run these tests.');
        }

        if (false == function_exists('GeoIP_record_by_addr')) {
            throw new \PHPUnit_Framework_SkippedTestError('The maxmind\'s official lib required to run these tests.');
        }
    }

    public static function provideIps()
    {
        return array(
            '24.24.24.24' => array('24.24.24.24', 'East Syracuse', 'United States'),
            '80.24.24.24' => array('80.24.24.24', 'Sabadell', 'Spain'),
        );
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgument
     * @expectedExceptionMessage Given MaxMind dat file "not_exist.dat" does not exist.
     */
    public function testThrowIfNotExistBinaryFileGiven()
    {
        new MaxMindBinary('not_exist.dat');
    }

    /**
     * @dataProvider provideIps
     */
    public function testLocationResultContainsExpectedFields($ip)
    {
        $provider = new MaxMindBinary($this->binaryFile);
        $results  = $provider->getGeocodedData($ip);

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('country', $result);
        $this->assertArrayHasKey('countryCode', $result);
        $this->assertArrayHasKey('regionCode', $result);
        $this->assertArrayHasKey('locality', $result);
        $this->assertArrayHasKey('latitude', $result);
        $this->assertArrayHasKey('longitude', $result);
        $this->assertArrayHasKey('postalCode', $result);
        $this->assertArrayHasKey('bounds', $result);
        $this->assertArrayHasKey('streetNumber', $result);
        $this->assertArrayHasKey('streetName', $result);
        $this->assertArrayHasKey('subLocality', $result);
        $this->assertArrayHasKey('county', $result);
        $this->assertArrayHasKey('countyCode', $result);
        $this->assertArrayHasKey('region', $result);
        $this->assertArrayHasKey('timezone', $result);
    }

    /**
     * @dataProvider provideIps
     */
    public function testFindLocationByIp($ip, $expectedCity, $expectedCountry)
    {
        $provider = new MaxMindBinary($this->binaryFile);
        $result   = $provider->getGeocodedData($ip);

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('locality', $result);
        $this->assertEquals($expectedCity, $result['locality']);
        $this->assertArrayHasKey('country', $result);
        $this->assertEquals($expectedCountry, $result['country']);
    }

    public function testShouldReturnResultsAsUtf8Encoded()
    {
        $provider = new MaxMindBinary($this->binaryFile);
        $results  = $provider->getGeocodedData('212.51.181.237');

        $this->assertSame('Châlette-sur-loing', $results[0]['locality']);
    }

    public function testGetName()
    {
        $provider = new MaxMindBinary($this->binaryFile);

        $this->assertEquals('maxmind_binary', $provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\NoResult
     * @expectedExceptionMessage No results found for IP address 127.0.0.1
     */
    public function testThrowIfIpAddressCouldNotBeLocated()
    {
        $provider = new MaxMindBinary($this->binaryFile);

        $provider->getGeocodedData('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MaxMindBinary does not support street addresses.
     */
    public function testThrowIfInvalidIpAddressGiven()
    {
        $provider = new MaxMindBinary($this->binaryFile);

        $provider->getGeocodedData('invalidIp');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MaxMindBinary is not able to do reverse geocoding.
     */
    public function testThrowOnReversedDataMethodUsage()
    {
        $provider = new MaxMindBinary($this->binaryFile);

        $provider->getReversedData(array());
    }
}
