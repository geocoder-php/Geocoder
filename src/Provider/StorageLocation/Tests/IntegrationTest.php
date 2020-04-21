<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\StorageLocation\Tests;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Geocoder\IntegrationTest\CachedResponseClient;
use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Model\AdminLevel;
use Geocoder\Model\AdminLevelCollection;
use Geocoder\Model\Bounds;
use Geocoder\Model\Coordinates;
use Geocoder\Model\Country;
use Geocoder\Provider\StorageLocation\DataBase\PsrCache;
use Geocoder\Provider\StorageLocation\Model\DBConfig;
use Geocoder\Provider\StorageLocation\Model\Place;
use Geocoder\Provider\StorageLocation\Model\Polygon;
use Geocoder\Provider\StorageLocation\StorageLocation;
use Geocoder\Query\ReverseQuery;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    const ELEM_LATITUDE = 'latitude';
    const ELEM_LONGITUDE = 'longitude';
    const ELEM_EXPECTED = 'expected';
    const ELEM_STREET_NUMBER = 'streetNumber';
    const ELEM_STREET_NAME = 'streetName';
    const ELEM_SUB_LOCALITY = 'subLocality';
    const ELEM_LOCALITY = 'locality';
    const ELEM_POSTAL_CODE = 'postalCode';

    protected $testIpv4 = false;

    protected $testIpv6 = false;

    protected $testHttpProvider = false;

    private $countCoordFiles = 0;

    /**
     * {@inheritdoc}
     */
    protected function createProvider(HttpClient $httpClient)
    {
        $dataBase = new PsrCache(new ArrayCachePool(), new DBConfig());
        $provider = new StorageLocation($dataBase);
        $this->loadJsonCoordinates($provider);

        return $provider;
    }

    /**
     * Test fetch address from nested polygons
     *
     * @dataProvider providerNestedPolygons
     *
     * @param float $lat
     * @param float $lon
     * @param array $expected
     *
     * @throws \Geocoder\Exception\Exception
     */
    public function testNestedPolygons(float $lat, float $lon, array $expected)
    {
        /** @var StorageLocation $provider */
        $provider = $this->createProvider($this->getCachedHttpClient());

        $result = $provider->reverseQuery(ReverseQuery::fromCoordinates($lat, $lon)->withLocale('en'));
        $address = $result->first();

        $this->assertEquals($expected[self::ELEM_STREET_NUMBER], $address->getStreetNumber());
        $this->assertEquals($expected[self::ELEM_STREET_NUMBER], $address->getStreetNumber());
        $this->assertEquals($expected[self::ELEM_SUB_LOCALITY], $address->getSubLocality());
        $this->assertEquals($expected[self::ELEM_LOCALITY], $address->getLocality());
        $this->assertEquals($expected[self::ELEM_POSTAL_CODE], $address->getPostalCode());
    }

    /**
     * @covers StorageLocation::getAllPlaces
     */
    public function testGetAllPlaces()
    {
        /** @var StorageLocation $provider */
        $provider = $this->createProvider($this->getCachedHttpClient());

        $totalCount = 0;
        $page = 0;
        while ($places = $provider->getAllPlaces($page * 50)) {
            foreach ($places as $place) {
                $this->assertEquals(Place::class, get_class($place));
                ++$totalCount;
            }
            ++$page;
        }
        $this->assertEquals($this->countCoordFiles, $totalCount);
    }

    /**
     * @covers StorageLocation::deletePlace
     */
    public function testDeletePlace()
    {
        /** @var StorageLocation $provider */
        $provider = $this->createProvider($this->getCachedHttpClient());

        $places = \SplFixedArray::fromArray($provider->getAllPlaces());
        $places->rewind();
        $provider->deletePlace($places->current());

        $totalCount = 0;
        $page = 0;
        while ($places = $provider->getAllPlaces($page * 50)) {
            $totalCount += count($places);
            ++$page;
        }
        $this->assertEquals($this->countCoordFiles - 1, $totalCount);
    }

    /**
     * @see testNestedPolygons
     * @case 1 Should return first, main layer
     * @case 2 Should return third, last layer
     * @case 3 Should return second layer, with elevated precise of coordinates
     *
     * @return iterable
     */
    public function providerNestedPolygons(): iterable
    {
        /* Altstadt, should be return first layer of coordinates, total Dusseldorf's Place */
        yield [
            self::ELEM_LATITUDE => 51.227546,
            self::ELEM_LONGITUDE => 6.784593,
            self::ELEM_EXPECTED => [
                self::ELEM_STREET_NUMBER => '',
                self::ELEM_STREET_NAME => '',
                self::ELEM_SUB_LOCALITY => 'Dusseldorf',
                self::ELEM_LOCALITY => 'North Rhine-Westphalia',
                self::ELEM_POSTAL_CODE => '',
            ]
        ];

        /* BestenPlatz, should be return third layer what nested inside other two layers */
        yield [
            self::ELEM_LATITUDE => 51.2314767,
            self::ELEM_LONGITUDE => 6.7473107,
            self::ELEM_EXPECTED => [
                self::ELEM_STREET_NUMBER => '1',
                self::ELEM_STREET_NAME => 'Belsenplatz',
                self::ELEM_SUB_LOCALITY => 'Dusseldorf',
                self::ELEM_LOCALITY => 'North Rhine-Westphalia',
                self::ELEM_POSTAL_CODE => '40545',
            ]
        ];

        /* LuegPlatz, should be return second layer what nested inside first layer */
        /* Additionally testing with elevated precise for coordinates */
        yield [
            self::ELEM_LATITUDE => 51.2314260099,
            self::ELEM_LONGITUDE => 6.7617290099,
            self::ELEM_EXPECTED => [
                self::ELEM_STREET_NUMBER => '',
                self::ELEM_STREET_NAME => '',
                self::ELEM_SUB_LOCALITY => 'Dusseldorf',
                self::ELEM_LOCALITY => 'North Rhine-Westphalia',
                self::ELEM_POSTAL_CODE => '40545',
            ]
        ];
    }

    /**
     * This client will make real request if cache was not found.
     *
     * @return CachedResponseClient
     */
    private function getCachedHttpClient()
    {
        try {
            $client = HttpClientDiscovery::find();
        } catch (\Http\Discovery\NotFoundException $e) {
            $client = $this->getMockForAbstractClass(HttpClient::class);

            $client
                ->expects($this->any())
                ->method('sendRequest')
                ->willThrowException($e);
        }

        return new CachedResponseClient($client, $this->getCacheDir(), $this->getApiKey());
    }

    /**
     * {@inheritdoc}
     */
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    /**
     * {@inheritdoc}
     */
    protected function getApiKey()
    {
        return '';
    }

    private function loadJsonCoordinates(StorageLocation $provider): bool
    {
        $success = true;
        $dirPath = __DIR__ . DIRECTORY_SEPARATOR . 'json-coordinates' . DIRECTORY_SEPARATOR;

        foreach (scandir($dirPath) as $file) {
            if (!is_file($dirPath.$file)) {
                continue;
            }
            $rawData = json_decode(file_get_contents($dirPath.$file), true);
            if (is_array($rawData)) {
                $provider->addPlace($this->mapRawDataToPlace($rawData));
                ++$this->countCoordFiles;
            } else {
                $success = false;
            }
        }

        return $success;
    }

    private function mapRawDataToPlace(array $rawData): Place
    {
        $root = $rawData['features'][0];

        $adminLevels = [];
        foreach ($root['properties']['geocoding']['admin'] as $adminLevel => $name) {
            $level = (int) substr($adminLevel, 5);
            if ($level > 5) {
                $level = 5;
            } elseif ($level < 1) {
                $level = 1;
            }

            $adminLevels[$level] = new AdminLevel($level, $name);
        }

        $polygons = [];
        foreach ($root['geometry']['coordinates'] as $rawPolygon) {
            $tempPolygon = new Polygon();
            foreach ($rawPolygon as $coordinates) {
                $tempPolygon->addCoordinates(new Coordinates($coordinates[1], $coordinates[0]));
            }
            $polygons[] = $tempPolygon;
        }

        return new Place(
            $rawData['geocoding']['attribution'],
            new AdminLevelCollection($adminLevels),
            null,
            new Bounds($root['bbox'][0], $root['bbox'][1], $root['bbox'][2], $root['bbox'][3]),
            $root['properties']['geocoding']['housenumber'] ?? '',
            $root['properties']['geocoding']['street'] ?? '',
            $root['properties']['geocoding']['postcode'] ?? '',
            $root['properties']['geocoding']['state'] ?? '',
            $root['properties']['geocoding']['city'] ?? '',
            new Country($root['properties']['geocoding']['country'], $root['properties']['geocoding']['country_code']),
            null,
            $polygons
        );
    }
}
