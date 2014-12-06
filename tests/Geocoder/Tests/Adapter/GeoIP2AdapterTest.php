<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Tests\Adapter;

use Geocoder\Adapter\GeoIP2Adapter;
use Geocoder\Tests\TestCase;
use Geocoder\Exception\RuntimeException;
use GeoIp2\Database\Reader;

/**
 * @author Jens Wiese <jens@howtrueisfalse.de>
 */
class GeoIP2AdapterTest extends TestCase
{
    /**
     * @var GeoIP2DatabaseAdapter
     */
    protected $adapter;

    /**
     * {@inheritdoc}
     * @throws RuntimeException
     */
    public static function setUpBeforeClass()
    {
        if (false === class_exists('\GeoIp2\Database\Reader')) {
            throw new RuntimeException("The maxmind's lib 'geoip2/geoip2' is required to run this test.");
        }
    }

    public function setUp()
    {
        $this->adapter = new GeoIP2Adapter($this->getGeoIP2ProviderMock());
    }

    public function testGetName()
    {
        $expectedName = 'maxmind_geoip2';
        $this->assertEquals($expectedName, $this->adapter->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgument
     * @expectedExceptionMessage must be called with a valid url. Got "127.0.0.1" instead.
     */
    public function testGetContentMustBeCalledWithUrl()
    {
        $url = '127.0.0.1';
        $this->adapter->getContent($url);
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgument
     * @expectedExceptionMessage URL must contain a valid query-string (an IP address, 127.0.0.1 for instance)
     */
    public function testAddressPassedToReaderMustBeIpAddress()
    {
        $url = 'file://database?not-valid=1';
        $this->adapter->getContent($url);
    }

    public static function provideDataForSwitchingRequestMethods()
    {
        return array(
            array(GeoIP2Adapter::GEOIP2_MODEL_CITY),
            array(GeoIP2Adapter::GEOIP2_MODEL_COUNTRY),
        );
    }

    /**
     * @dataProvider provideDataForSwitchingRequestMethods
     */
    public function testIpAddressIsPassedCorrectToReader($geoIp2Model)
    {
        $geoIp2Provider = $this->getGeoIP2ProviderMock();
        $geoIp2Provider
            ->expects($this->any())
            ->method($geoIp2Model)
            ->with('127.0.0.1')
            ->will($this->returnValue(
                $this->getGeoIP2ModelMock($geoIp2Model)
            ));

        $adapter = new GeoIP2Adapter($geoIp2Provider, $geoIp2Model);
        $adapter->getContent('file://geoip?127.0.0.1');
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedOperation
     * @expectedExceptionMessage Model "unsupported_model" is not available.
     */
    public function testNotSupportedGeoIP2ModelLeadsToException()
    {
        new GeoIP2Adapter($this->getGeoIP2ProviderMock(), 'unsupported_model');
    }

    public function testSettingLocaleIsCorrect()
    {
        $this->assertNull($this->adapter->getLocale());

        $expectedLocale = 'it';
        $this->adapter->setLocale($expectedLocale);

        $this->assertEquals($expectedLocale, $this->adapter->getLocale());
    }

    public function testReaderResponseIsJsonEncoded()
    {
        $cityModel = $this->getGeoIP2ModelMock(GeoIP2Adapter::GEOIP2_MODEL_CITY);

        $geoIp2Provider = $this->getGeoIP2ProviderMock();
        $geoIp2Provider
            ->expects($this->any())
            ->method('city')
            ->will($this->returnValue($cityModel));

        $adapter = new GeoIP2Adapter($geoIp2Provider);

        $result = $adapter->getContent('file://database?127.0.0.1');
        $this->assertJson($result);

        $decodedResult = json_decode($result);
        $this->assertObjectHasAttribute('city', $decodedResult);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getGeoIP2ProviderMock()
    {
        $mock = $this->getMockBuilder('\GeoIp2\ProviderInterface')->getMock();

        return $mock;
    }

    /**
     * @param  int                                      $geoIP2Model (e.g. GeoIP2Adapter::GEOIP2_MODEL_CITY, ...)
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getGeoIP2ModelMock($geoIP2Model)
    {
        $mockClass = '\\GeoIp2\\Model\\' . ucfirst($geoIP2Model);

        $mock = $this->getMockBuilder($mockClass)->disableOriginalConstructor()->getMock();
        $mock
            ->expects($this->any())
            ->method('jsonSerialize')
            ->will($this->returnValue(
                array(
                    'city' => array(
                        'geoname_id' => 2911298,
                        'names' => array(
                              'de' => 'Hamburg',
                              'en' => 'Hamburg',
                              'es' => 'Hamburgo',
                              'fr' => 'Hambourg',
                              'ja' => 'ハンブルク',
                              'pt-BR' => 'Hamburgo',
                              'ru' => 'Гамбург',
                              'zh-CN' => '汉堡市',
                        )
                    )
                )
            ));

        return $mock;
    }
}
