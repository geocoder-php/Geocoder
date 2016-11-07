<?php
namespace Geocoder\Tests\Provider;

use Geocoder\Location;
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

    public function testLocationResultContainsExpectedFieldsForAnAmericanIp()
    {
        $provider = new MaxMindBinary($this->binaryFile);
        $results  = $provider->geocode('24.24.24.24');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);

        $this->assertEquals('43.089200000000005', $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals('-76.025000000000006', $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('East Syracuse', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('NY', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('United States', $result->getCountry()->getName());
        $this->assertEquals('US', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    public function testLocationResultContainsExpectedFieldsForASpanishIp()
    {
        $provider = new MaxMindBinary($this->binaryFile);
        $results  = $provider->geocode('80.24.24.24');

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);

        $this->assertEquals('41.543299999999988', $result->getCoordinates()->getLatitude(), '', 0.001);
        $this->assertEquals('2.1093999999999937', $result->getCoordinates()->getLongitude(), '', 0.001);
        $this->assertNull($result->getBounds());
        $this->assertNull($result->getStreetNumber());
        $this->assertNull($result->getStreetName());
        $this->assertNull($result->getPostalCode());
        $this->assertEquals('Sabadell', $result->getLocality());
        $this->assertNull($result->getSubLocality());
        $this->assertCount(1, $result->getAdminLevels());
        $this->assertEquals('56', $result->getAdminLevels()->get(1)->getName());
        $this->assertNull($result->getAdminLevels()->get(1)->getCode());
        $this->assertEquals('Spain', $result->getCountry()->getName());
        $this->assertEquals('ES', $result->getCountry()->getCode());
        $this->assertNull($result->getTimezone());
    }

    /**
     * @dataProvider provideIps
     */
    public function testFindLocationByIp($ip, $expectedCity, $expectedCountry)
    {
        $provider = new MaxMindBinary($this->binaryFile);
        $results  = $provider->geocode($ip);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf('\Geocoder\Model\Address', $result);
        $this->assertEquals($expectedCity, $result->getLocality());
        $this->assertEquals($expectedCountry, $result->getCountry()->getName());
    }

    public function testShouldReturnResultsAsUtf8Encoded()
    {
        $provider = new MaxMindBinary($this->binaryFile);
        $results  = $provider->geocode('212.51.181.237');

        /** @var Location $result */
        $result = $results->first();
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
     * @expectedExceptionMessage The MaxMindBinary provider does not support IPv6 addresses.
     */
    public function testThrowIfIpAddressIsNotIpV4()
    {
        $provider = new MaxMindBinary($this->binaryFile);

        $provider->geocode('2002:5018:1818:0:0:0:0:0');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage The MaxMindBinary provider does not support street addresses.
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
    public function testThrowOnReverseMethodUsage()
    {
        $provider = new MaxMindBinary($this->binaryFile);

        $provider->reverse(0, 0);
    }
}
