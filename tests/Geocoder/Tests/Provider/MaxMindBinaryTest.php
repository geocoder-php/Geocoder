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
        $results  = $provider->geocode($ip);

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);

        $this->assertNull($result->getLatitude());
        $this->assertNull($result->getLongitude());
        $this->assertFalse($result->getBounds()->isDefined());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertNotNull($result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertNull($result->getCounty()->getName());
        $this->assertNull($result->getCounty()->getCode());
        $this->assertNull($result->getRegion()->getName());
        $this->assertNull($result->getRegion()->getCode());
        $this->assertNotNull($result->getCountry()->getName());
        $this->assertNull($result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    /**
     * @dataProvider provideIps
     */
    public function testFindLocationByIp($ip, $expectedCity, $expectedCountry)
    {
        $provider = new MaxMindBinary($this->binaryFile);
        $results  = $provider->geocode($ip);

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals($expectedCity, $result->getLocality());
        $this->assertEquals($expectedCountry, $result->getCountry()->getName());
    }

    public function testShouldReturnResultsAsUtf8Encoded()
    {
        $provider = new MaxMindBinary($this->binaryFile);
        $results  = $provider->geocode('212.51.181.237');

        /** @var \Geocoder\Model\Address $result */
        $result = $results[0];
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertSame('Châlette-sur-loing', $result->getLocality());
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

        $provider->geocode('127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MaxMindBinary does not support street addresses.
     */
    public function testThrowIfInvalidIpAddressGiven()
    {
        $provider = new MaxMindBinary($this->binaryFile);

        $provider->geocode('invalidIp');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MaxMindBinary is not able to do reverse geocoding.
     */
    public function testThrowOnReversedDataMethodUsage()
    {
        $provider = new MaxMindBinary($this->binaryFile);

        $provider->reverse(0, 0);
    }
}
