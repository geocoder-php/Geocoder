<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\StorageLocation;

use Geocoder\Collection;
use Geocoder\Model\Address;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\AdminLevel;
use Geocoder\Model\AdminLevelCollection;
use Geocoder\Model\Coordinates;
use Geocoder\Provider\Provider;
use Geocoder\Provider\StorageLocation\DataBase\DataBaseInterface;
use Geocoder\Provider\StorageLocation\Model\Place;
use Geocoder\Provider\StorageLocation\Model\Polygon;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
class StorageLocation implements Provider
{
    /**
     * @var DataBaseInterface
     */
    private $dataBase;

    public function __construct(DataBaseInterface $dataBase)
    {
        $this->dataBase = $dataBase;
    }

    public function addPlace(Place $place): bool
    {
        return $this->dataBase->add($place);
    }

    public function deletePlace(Place $place): bool
    {
        return $this->dataBase->delete($place);
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return Place[]
     */
    public function getAllPlaces(int $offset = 0, int $limit = 50): array
    {
        return $this->dataBase->getAllPlaces($offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $result = [];
        $places = $this->dataBase->get(
            $this->dataBase->normalizeStringForKeyName($query->getText()),
            0,
            $query->getLimit(),
            $query->getLocale() ? $query->getLocale() : ''
        );

        foreach ($places as $place) {
            $result = array_merge($result, $place->getAvailableAddresses());
        }

        return new AddressCollection($result);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $result = $this->findPlaceByCoordinates($query->getCoordinates(), $query->getLocale() ? $query->getLocale() : '');

        return new AddressCollection($result ? $result->getAvailableAddresses() : []);
    }

    /**
     * @param Coordinates $coordinates
     * @param string $locale
     *
     * @return Place|null
     */
    private function findPlaceByCoordinates(Coordinates $coordinates, string $locale = '')
    {
        $levels = $this->dataBase->getAdminLevels();
        asort($levels);

        /** @var Place|null $result */
        $result = null;
        foreach ($levels as $level) {
            $result ?
                $tempPlace = $result->getSelectedAddress() :
                $tempPlace = new Address($this->getName(), new AdminLevelCollection([new AdminLevel($level, ',')]));

            $page = 0;
            while ($possiblePlaces = $this->dataBase->get(
                $this->dataBase->compileKey($tempPlace, true, true, false),
                $page,
                $this->dataBase->getDbConfig()->getMaxPlacesInOneResponse(),
                $locale
            )) {
                foreach ($possiblePlaces as $place) {
                    if ($result && $level <= $place->getMaxAdminLevel()) {
                        continue;
                    }

                    foreach ($place->getPolygons() as $polygon) {
                        if ($this->checkCoordInBundle($coordinates->getLatitude(), $coordinates->getLongitude(), $polygon)) {
                            $result = $place;

                            break 3;
                        }
                    }
                }

                ++$page;
            }

            break;
        }

        return $result;
    }

    /**
     * Check bundle for coordinates
     *
     * @param float   $latitude
     * @param float   $longitude
     * @param Polygon $polygon
     *
     * @return bool|int
     */
    private function checkCoordInBundle(float $latitude, float $longitude, Polygon $polygon)
    {
        $vertices_x = [];
        $vertices_y = [];

        foreach ($polygon->getCoordinates() as $coordinate) {
            $vertices_x[] = $coordinate->getLongitude();
            $vertices_y[] = $coordinate->getLatitude();
        }

        $points_polygon = count($vertices_x) - 1;

        return $this->isInPolygon($points_polygon, $vertices_x, $vertices_y, $longitude, $latitude);
    }

    /**
     * Check polygon for interesecting specific coordinates
     *
     * @param $points_polygon
     * @param $vertices_x
     * @param $vertices_y
     * @param $longitude_x
     * @param $latitude_y
     *
     * @return bool|int
     */
    private function isInPolygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)
    {
        $i = $j = $c = 0;
        for ($i = 0, $j = $points_polygon; $i < $points_polygon; $j = $i++) {
            if ((($vertices_y[$i] > $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
                ($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]))) {
                $c = !$c;
            }
        }

        return $c;
    }

    /**
     * Calculate central coordinate
     *
     * @param Coordinates[] $coordinates
     *
     * @return Coordinates
     */
    private function getCentralCoordinate(array $coordinates): Coordinates
    {
        $total = count($coordinates);
        if (1 === $total) {
            return current($coordinates);
        }

        $x = $y = $z = 0;
        foreach ($coordinates as $coordinate) {
            $lat = deg2rad($coordinate->getLatitude());
            $lon = deg2rad($coordinate->getLongitude());

            $x += cos($lat) * cos($lon);
            $y += cos($lat) * sin($lon);
            $z += sin($lat);
        }

        $x = $x / $total;
        $y = $y / $total;
        $z = $z / $total;

        $centralLongitude = atan2($y, $x);
        $centralSquareRoot = sqrt(($x * $x) + ($y * $y));
        $centralLatitude = atan2($z, $centralSquareRoot);

        return new Coordinates(rad2deg($centralLatitude), rad2deg($centralLongitude));
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'storageLocation';
    }
}
