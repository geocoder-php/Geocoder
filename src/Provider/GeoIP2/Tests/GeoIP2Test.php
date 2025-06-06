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

use Geocoder\Collection;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\IntegrationTest\BaseTestCase;
use Geocoder\Location;
use Geocoder\Provider\GeoIP2\GeoIP2;
use Geocoder\Provider\GeoIP2\GeoIP2Adapter;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AuthenticationException;
use GeoIp2\Exception\OutOfQueriesException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author Jens Wiese <jens@howtrueisfalse.de>
 */
class GeoIP2Test extends BaseTestCase
{
    /**
     * @var GeoIP2
     */
    protected $provider;

    public function setUp(): void
    {
        $this->provider = new GeoIP2($this->getGeoIP2AdapterMock());
    }

    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    public function testGetName(): void
    {
        $this->assertEquals('geoip2', $this->provider->getName());
    }

    public function testQueryingReverseLeadsToException(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The GeoIP2 provider is not able to do reverse geocoding.');

        $this->provider->reverseQuery(ReverseQuery::fromCoordinates(50, 9));
    }

    public function testGeocodeWithLocalhostIPv4(): void
    {
        $results = $this->provider->geocodeQuery(GeocodeQuery::create('127.0.0.1'));

        $this->assertInstanceOf(\Geocoder\Model\AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf(\Geocoder\Model\Address::class, $result);
        $this->assertEquals('localhost', $result->getLocality());
        $this->assertEquals('localhost', $result->getCountry()->getName());
    }

    public function testOnlyIpAddressesCouldBeResolved(): void
    {
        $this->expectException(\Geocoder\Exception\UnsupportedOperation::class);
        $this->expectExceptionMessage('The GeoIP2 provider does not support street addresses, only IP addresses.');

        $this->provider->geocodeQuery(GeocodeQuery::create('Street 123, Somewhere'));
    }

    /**
     * Provides data for geocode test.
     *
     * @return array<string, array<array<string, array<array<string, string>>|float|int|string|null>|string>>
     */
    public static function provideDataForRetrievingGeodata()
    {
        $testdata = [
            'Response with data' => [
                '74.200.247.59',
                '{"city":{"geoname_id":2911298,"names":{"de":"Hamburg","en":"Hamburg","es":"Hamburgo","fr":"Hambourg","ja":"\u30cf\u30f3\u30d6\u30eb\u30af","pt-BR":"Hamburgo","ru":"\u0413\u0430\u043c\u0431\u0443\u0440\u0433","zh-CN":"\u6c49\u5821\u5e02"}},"continent":{"code":"EU","geoname_id":6255148,"names":{"de":"Europa","en":"Europe","es":"Europa","fr":"Europe","ja":"\u30e8\u30fc\u30ed\u30c3\u30d1","pt-BR":"Europa","ru":"\u0415\u0432\u0440\u043e\u043f\u0430","zh-CN":"\u6b27\u6d32"}},"country":{"geoname_id":2921044,"iso_code":"DE","names":{"de":"Deutschland","en":"Germany","es":"Alemania","fr":"Allemagne","ja":"\u30c9\u30a4\u30c4\u9023\u90a6\u5171\u548c\u56fd","pt-BR":"Alemanha","ru":"\u0413\u0435\u0440\u043c\u0430\u043d\u0438\u044f","zh-CN":"\u5fb7\u56fd"}},"location":{"latitude":53.55,"longitude":10,"time_zone":"Europe\/Berlin"},"registered_country":{"geoname_id":2921044,"iso_code":"DE","names":{"de":"Deutschland","en":"Germany","es":"Alemania","fr":"Allemagne","ja":"\u30c9\u30a4\u30c4\u9023\u90a6\u5171\u548c\u56fd","pt-BR":"Alemanha","ru":"\u0413\u0435\u0440\u043c\u0430\u043d\u0438\u044f","zh-CN":"\u5fb7\u56fd"}},"subdivisions":[{"geoname_id":2911297,"iso_code":"HH","names":{"de":"Hamburg","en":"Hamburg","es":"Hamburgo","fr":"Hambourg"}}],"traits":{"ip_address":"74.200.247.59"},"postal":{"code":"EC4N"}}',
                [
                    'latitude' => 53.55,
                    'longitude' => 10,
                    'boundsDefined' => null,
                    'streetNumber' => null,
                    'streetName' => null,
                    'locality' => 'Hamburg',
                    'subLocality' => null,
                    'postalCode' => 'EC4N',
                    'adminLevels' => [1 => [
                        'name' => 'Hamburg',
                        'code' => 'HH',
                    ]],
                    'country' => 'Germany',
                    'countryCode' => 'DE',
                    'timezone' => 'Europe/Berlin',
                ],
            ],
            'Response with all possible data' => [
                '93.36.20.217',
                '{"country": {"iso_code": "IT","names": {"pt-BR": "Itália","es": "Italia","ru": "Италия","en": "Italy","zh-CN": "意大利","fr": "Italie","de": "Italien","ja": "イタリア共和国"},"geoname_id": 3175395},"location": {"longitude": 9.2667,"latitude": 45.5833,"time_zone": "Europe/Rome"},"subdivisions": [{"iso_code": "25","names": {"en": "Lombardy","fr": "Lombardie","de": "Lombardei","es": "Lombardía"},"geoname_id": 3174618},{"iso_code": "MB","names": {"en": "Monza Brianza"},"geoname_id": 6955700}],"postal": {"code": "20900"},"city": {"names": {"pt-BR": "Monza","es": "Monza","ru": "Монца","en": "Monza","zh-CN": "蒙扎","fr": "Monza","de": "Monza","ja": "モンツァ"},"geoname_id": 3172629},"continent": {"names": {"pt-BR": "Europa","es": "Europa","ru": "Европа","en": "Europe","zh-CN": "欧洲","fr": "Europe","de": "Europa","ja": "ヨーロッパ"},"geoname_id": 6255148,"code": "EU"},"registered_country": {"iso_code": "IT","names": {"pt-BR": "Itália","es": "Italia","ru": "Италия","en": "Italy","zh-CN": "意大利","fr": "Italie","de": "Italien","ja": "イタリア共和国"},"geoname_id": 3175395},"traits": {"domain": "fastwebnet.it","autonomous_system_number": 12874,"ip_address": "93.36.20.217","organization": "Fastweb","isp": "Fastweb","autonomous_system_organization": "Fastweb SpA"},"represented_country": {"names": {}}}',
                [
                    'latitude' => 45.5833,
                    'longitude' => 9.2667,
                    'boundsDefined' => null,
                    'streetNumber' => null,
                    'streetName' => null,
                    'locality' => 'Monza',
                    'subLocality' => null,
                    'postalCode' => '20900',
                    'adminLevels' => [
                        1 => [
                            'name' => 'Lombardy',
                            'code' => '25',
                        ],
                        2 => [
                            'name' => 'Monza Brianza',
                            'code' => 'MB',
                        ],
                    ],
                    'country' => 'Italy',
                    'countryCode' => 'IT',
                    'timezone' => 'Europe/Rome',
                ],
            ],
            'Response with all data null' => [
                '74.200.247.59',
                '{}',
                [
                    'latitude' => null,
                    'longitude' => null,
                    'boundsDefined' => null,
                    'streetNumber' => null,
                    'streetName' => null,
                    'locality' => null,
                    'subLocality' => null,
                    'postalCode' => null,
                    'adminLevels' => [],
                    'country' => null,
                    'countryCode' => null,
                    'timezone' => null,
                ],
            ],
        ];

        return $testdata;
    }

    /**
     * @dataProvider provideDataForRetrievingGeodata
     *
     * @param array<string, mixed> $expectedGeodata
     */
    public function testRetrievingGeodata(string $address, string $adapterResponse, array $expectedGeodata): void
    {
        $adapter = $this->getGeoIP2AdapterMock($adapterResponse);
        $provider = new GeoIP2($adapter);

        $results = $provider->geocodeQuery(GeocodeQuery::create($address));

        $this->assertInstanceOf(\Geocoder\Model\AddressCollection::class, $results);
        $this->assertCount(1, $results);

        /** @var Location $result */
        $result = $results->first();
        $this->assertInstanceOf(\Geocoder\Model\Address::class, $result);
        if (isset($expectedGeodata['latitude'])) {
            $this->assertEquals($expectedGeodata['latitude'], $result->getCoordinates()->getLatitude());
            $this->assertEquals($expectedGeodata['longitude'], $result->getCoordinates()->getLongitude());
        } else {
            $this->assertNull($result->getCoordinates());
        }

        if ($expectedGeodata['boundsDefined']) {
            $this->assertNotNull($result->getBounds());
        } else {
            $this->assertNull($result->getBounds());
        }

        $countryName = null;
        $countryCode = null;
        if (null !== $country = $result->getCountry()) {
            $countryName = $country->getName();
            $countryCode = $country->getCode();
        }

        $this->assertEquals($expectedGeodata['streetNumber'], $result->getStreetNumber());
        $this->assertEquals($expectedGeodata['streetName'], $result->getStreetName());
        $this->assertEquals($expectedGeodata['locality'], $result->getLocality());
        $this->assertEquals($expectedGeodata['subLocality'], $result->getSubLocality());
        $this->assertEquals($expectedGeodata['postalCode'], $result->getPostalCode());
        $this->assertEquals($expectedGeodata['country'], $countryName);
        $this->assertEquals($expectedGeodata['countryCode'], $countryCode);
        $this->assertEquals($expectedGeodata['timezone'], $result->getTimezone());
        foreach ($expectedGeodata['adminLevels'] as $level => $data) {
            $this->assertEquals($data['name'], $result->getAdminLevels()->get($level)->getName());
            $this->assertEquals($data['code'], $result->getAdminLevels()->get($level)->getCode());
        }
    }

    public function testRetrievingGeodataNotExistingLocation(): void
    {
        $adapter = $this->getGeoIP2AdapterMock('');
        $provider = new GeoIP2($adapter);

        $result = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, $result->count());
    }

    public function testGeoIp2Encoding(): void
    {
        $reader = new Reader(__DIR__.'/fixtures/GeoLite2-City.mmdb');
        $adapter = new GeoIP2Adapter($reader);
        $provider = new GeoIP2($adapter);
        $locality = $provider->geocodeQuery(GeocodeQuery::create('79.114.34.148'))->first()->getLocality();
        $this->assertEquals('Timișoara', $locality);
    }

    /**
     * @dataProvider provideDataForTestingExceptions
     */
    public function testExceptionConversion(\Exception $original, string $replacementClass): void
    {
        $adapter = $this->getGeoIP2AdapterMock($original);
        $provider = new GeoIP2($adapter);

        self::expectException($replacementClass);
        self::expectExceptionMessage($original->getMessage());
        self::expectExceptionCode($original->getCode());

        $results = $provider->geocodeQuery(GeocodeQuery::create('74.200.247.59'));
    }

    /**
     * @return array<array<AuthenticationException|OutOfQueriesException|class-string>>
     */
    public static function provideDataForTestingExceptions(): array
    {
        return [
            [new AuthenticationException('Credentials are no good'), InvalidCredentials::class],
            [new OutOfQueriesException('You ran out'), QuotaExceeded::class],
        ];
    }

    /**
     * @param \Exception|string $returnValue
     *
     * @return GeoIP2Adapter&MockObject
     */
    private function getGeoIP2AdapterMock($returnValue = '')
    {
        $mock = $this->createPartialMock(GeoIP2Adapter::class, ['getContent']);

        if ($returnValue instanceof \Exception) {
            $returnValue = $this->throwException($returnValue);
        } else {
            $returnValue = $this->returnValue($returnValue);
        }

        $mock->expects($this->any())->method('getContent')->will($returnValue);

        return $mock;
    }
}
