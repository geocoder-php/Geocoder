<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GeoIP2\Tests;

use Geocoder\Provider\GeoIP2\GeoIP2Adapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Jens Wiese <jens@howtrueisfalse.de>
 */
class GeoIP2AdapterTest extends TestCase
{
    /**
     * @var GeoIP2Adapter
     */
    protected $adapter;

    /**
     * @throws \RuntimeException
     */
    public static function setUpBeforeClass(): void
    {
        if (false === class_exists(\GeoIp2\Database\Reader::class)) {
            throw new \RuntimeException("The maxmind's lib 'geoip2/geoip2' is required to run this test.");
        }
    }

    public function setUp(): void
    {
        $this->adapter = new GeoIP2Adapter($this->getGeoIP2ProviderMock());
    }

    public function testGetName(): void
    {
        $expectedName = 'maxmind_geoip2';
        $this->assertEquals($expectedName, $this->adapter->getName());
    }

    public function testGetContentMustBeCalledWithUrl(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidArgument::class);
        $this->expectExceptionMessage('must be called with a valid url. Got "127.0.0.1" instead.');

        $url = '127.0.0.1';
        $this->adapter->getContent($url);
    }

    public function testAddressPassedToReaderMustBeIpAddress(): void
    {
        $this->expectException(\Geocoder\Exception\InvalidArgument::class);
        $this->expectExceptionMessage('URL must contain a valid query-string (an IP address, 127.0.0.1 for instance)');

        $url = 'file://database?not-valid=1';
        $this->adapter->getContent($url);
    }

    /**
     * @return array<string[]>
     */
    public static function provideDataForSwitchingRequestMethods(): array
    {
        return [
            [GeoIP2Adapter::GEOIP2_MODEL_CITY],
            [GeoIP2Adapter::GEOIP2_MODEL_COUNTRY],
        ];
    }

    /**
     * @dataProvider provideDataForSwitchingRequestMethods
     */
    public function testIpAddressIsPassedCorrectToReader(string $geoIp2Model): void
    {
        $geoIp2Provider = $this->getGeoIP2ProviderMock();
        $geoIp2Provider
            ->expects($this->once())
            ->method($geoIp2Model)
            ->with('127.0.0.1')
            ->willReturn($this->getGeoIP2ModelMock($geoIp2Model));

        $adapter = new GeoIP2Adapter($geoIp2Provider, $geoIp2Model);
        $adapter->getContent('file://geoip?127.0.0.1');
    }

    public function testNotSupportedGeoIP2ModelLeadsToException(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('Model "unsupported_model" is not available.');

        new GeoIP2Adapter($this->getGeoIP2ProviderMock(), 'unsupported_model');
    }

    public function testReaderResponseIsJsonEncoded(): void
    {
        $cityModel = $this->getGeoIP2ModelMock(GeoIP2Adapter::GEOIP2_MODEL_CITY);

        $geoIp2Provider = $this->getGeoIP2ProviderMock();
        $geoIp2Provider
            ->expects($this->any())
            ->method('city')
            ->willReturn($cityModel);

        $adapter = new GeoIP2Adapter($geoIp2Provider);

        $result = $adapter->getContent('file://database?127.0.0.1');
        $this->assertJson($result);

        $decodedResult = json_decode($result);
        $this->assertObjectHasProperty('city', $decodedResult);
    }

    /**
     * @return MockObject
     */
    protected function getGeoIP2ProviderMock()
    {
        $mock = $this->getMockBuilder(\GeoIp2\ProviderInterface::class)->getMock();

        return $mock;
    }

    /**
     * @param string $geoIP2Model (e.g. GeoIP2Adapter::GEOIP2_MODEL_CITY, ...)
     *
     * @return MockObject
     */
    protected function getGeoIP2ModelMock($geoIP2Model)
    {
        /** @var class-string $mockClass */
        $mockClass = '\\GeoIp2\\Model\\'.ucfirst($geoIP2Model);

        $mock = $this->getMockBuilder($mockClass)->disableOriginalConstructor()->getMock();
        $mock
            ->expects($this->any())
            ->method('jsonSerialize')
            ->willReturn([
                'city' => [
                    'geoname_id' => 2911298,
                    'names' => [
                        'de' => 'Hamburg',
                        'en' => 'Hamburg',
                        'es' => 'Hamburgo',
                        'fr' => 'Hambourg',
                        'ja' => 'ハンブルク',
                        'pt-BR' => 'Hamburgo',
                        'ru' => 'Гамбург',
                        'zh-CN' => '汉堡市',
                    ],
                ],
            ]);

        return $mock;
    }
}
