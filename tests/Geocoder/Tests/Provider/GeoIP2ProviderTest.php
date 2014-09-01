<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Tests\Provider;

use Geocoder\Exception\NoResultException;
use Geocoder\HttpAdapter\CurlHttpAdapter;
use Geocoder\Provider\GeoIP2Provider;
use Geocoder\Tests\TestCase;

/**
 * @author Jens Wiese <jens@howtrueisfalse.de>
 */
class GeoIP2ProviderTest extends TestCase
{
    /**
     * @var GeoIP2Provider
     */
    protected $provider;

    public function setUp()
    {
        $this->provider = new GeoIP2Provider($this->getGeoIP2AdapterMock());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgumentException
     * @expectedExceptionMessage GeoIP2Adapter is needed in order to access the GeoIP2 service.
     */
    public function testWrongAdapterLeadsToException()
    {
        new GeoIP2Provider(new CurlHttpAdapter());
    }

    public function testGetName()
    {
        $expectedName = 'maxmind_geoip2';
        $this->assertEquals($expectedName, $this->provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The Geocoder\Provider\GeoIP2Provider is not able to do reverse geocoding.
     */
    public function testQueryingReversedDataLeadToException()
    {
        $this->provider->getReversedData(array(50, 9));
    }

    public function testLocalhostDefaults()
    {
        $expectedResult = array(
            'city'      => 'localhost',
            'region'    => 'localhost',
            'county'    => 'localhost',
            'country'   => 'localhost',
        );

        $actualResult = $this->provider->getGeocodedData('127.0.0.1');

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The Geocoder\Provider\GeoIP2Provider does not support street addresses.
     */
    public function testOnlyIpAddressesCouldBeResolved()
    {
        $this->provider->getGeocodedData('Street 123, Somewhere');
    }

    /**
     * Provides data for getGeocodedData test
     *
     * @return array
     */
    public static function provideDataForRetrievingGeodata()
    {
        $testdata = array(
            'Response with all possible data' => array(
                '74.200.247.59',
                '{"city":{"geoname_id":2911298,"names":{"de":"Hamburg","en":"Hamburg","es":"Hamburgo","fr":"Hambourg","ja":"\u30cf\u30f3\u30d6\u30eb\u30af","pt-BR":"Hamburgo","ru":"\u0413\u0430\u043c\u0431\u0443\u0440\u0433","zh-CN":"\u6c49\u5821\u5e02"}},"continent":{"code":"EU","geoname_id":6255148,"names":{"de":"Europa","en":"Europe","es":"Europa","fr":"Europe","ja":"\u30e8\u30fc\u30ed\u30c3\u30d1","pt-BR":"Europa","ru":"\u0415\u0432\u0440\u043e\u043f\u0430","zh-CN":"\u6b27\u6d32"}},"country":{"geoname_id":2921044,"iso_code":"DE","names":{"de":"Deutschland","en":"Germany","es":"Alemania","fr":"Allemagne","ja":"\u30c9\u30a4\u30c4\u9023\u90a6\u5171\u548c\u56fd","pt-BR":"Alemanha","ru":"\u0413\u0435\u0440\u043c\u0430\u043d\u0438\u044f","zh-CN":"\u5fb7\u56fd"}},"location":{"latitude":53.55,"longitude":10,"time_zone":"Europe\/Berlin"},"registered_country":{"geoname_id":2921044,"iso_code":"DE","names":{"de":"Deutschland","en":"Germany","es":"Alemania","fr":"Allemagne","ja":"\u30c9\u30a4\u30c4\u9023\u90a6\u5171\u548c\u56fd","pt-BR":"Alemanha","ru":"\u0413\u0435\u0440\u043c\u0430\u043d\u0438\u044f","zh-CN":"\u5fb7\u56fd"}},"subdivisions":[{"geoname_id":2911297,"iso_code":"HH","names":{"de":"Hamburg","en":"Hamburg","es":"Hamburgo","fr":"Hambourg"}}],"traits":{"ip_address":"74.200.247.59"}}',
                array(
                    'latitude' => 53.55,
                    'longitude' => 10,
                    'bounds' => null,
                    'streetNumber' => null,
                    'streetName' => null,
                    'city' => 'Hamburg',
                    'zipcode' => null,
                    'cityDistrict' => null,
                    'county' => null,
                    'countyCode' => null,
                    'region' => 'Hamburg',
                    'regionCode' => 'HH',
                    'country' => 'Germany',
                    'countryCode' => 'DE',
                    'timezone' => null,
                )
            ),
            'Response with all data null' => array(
                '74.200.247.59',
                '{}',
                array(
                    'latitude' => null,
                    'longitude' => null,
                    'bounds' => null,
                    'streetNumber' => null,
                    'streetName' => null,
                    'city' => null,
                    'zipcode' => null,
                    'cityDistrict' => null,
                    'county' => null,
                    'countyCode' => null,
                    'region' => null,
                    'regionCode' => null,
                    'country' => null,
                    'countryCode' => null,
                    'timezone' => null,
                )
            )
        );

        return $testdata;
    }

    /**
     * @dataProvider provideDataForRetrievingGeodata
     * @param string $address
     * @param $adapterResponse
     * @param $expectedGeodata
     */
    public function testRetrievingGeodata($address, $adapterResponse, $expectedGeodata)
    {
        $adapter = $this->getGeoIP2AdapterMock($adapterResponse);
        $provider = new GeoIP2Provider($adapter);

        $actualGeodata = $provider->getGeocodedData($address);

        $this->assertSame($expectedGeodata, $actualGeodata[0]);
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage No results found for IP address 74.200.247.59
     */
    public function testRetrievingGeodataNotExistingLocation()
    {
        $adapterReturn = new NoResultException('No results found for IP address 74.200.247.59');
        $adapter = $this->getGeoIP2AdapterMock($adapterReturn);

        $provider = new GeoIP2Provider($adapter);

        $provider->getGeocodedData('74.200.247.59');
    }

    /**
     * @param  mixed                                    $returnValue
     * @return \PHPUnit_Framework_MockObject_MockObject | GeoIP2DatabaseAdapter
     */
    public function getGeoIP2AdapterMock($returnValue = '')
    {
        $mock = $this->getMockBuilder('\Geocoder\HttpAdapter\GeoIP2Adapter')->disableOriginalConstructor()->getMock();

        if ($returnValue instanceof \Exception) {
            $returnValue = $this->throwException($returnValue);
        } else {
            $returnValue = $this->returnValue($returnValue);
        }

        $mock->expects($this->any())->method('setLocale')->will($this->returnSelf());
        $mock->expects($this->any())->method('getContent')->will($returnValue);

        return $mock;
    }
}
