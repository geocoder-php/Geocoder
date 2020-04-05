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
use Http\Client\HttpClient;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected $testIpv4 = false;

    protected $testIpv6 = false;

    protected $testHttpProvider = false;

    /**
     * {@inheritdoc}
     */
    protected function createProvider(HttpClient $httpClient)
    {
        $dataBase = new PsrCache(new FilesystemAdapter(), new DBConfig());
        $provider = new StorageLocation($dataBase);

        $rawData = file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, 'json-coordinates', 'london.json']));
        $provider->addPlace($this->mapRawDataToPlace(json_decode($rawData, true)));

        $rawData = file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, 'json-coordinates', 'white-house.json']));
        $provider->addPlace($this->mapRawDataToPlace(json_decode($rawData, true)));

        return $provider;
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
