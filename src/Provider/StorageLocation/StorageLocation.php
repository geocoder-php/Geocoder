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
     * @inheritDoc
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $result = [];
        foreach ($this->dataBase->get($this->dataBase->normalizeStringForKeyName($query->getText())) as $place) {
            $result[] = $this->mapPlaceToAddress($place);
        }

        return new AddressCollection($result);
    }

    /**
     * @inheritDoc
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $result = $this->findPlaceByCoordinates($query->getCoordinates());

        return new AddressCollection($result ? [$this->mapPlaceToAddress($result)] : []);
    }

    private function mapPlaceToAddress(Place $place): Address
    {
        $tempResults = [];
        foreach ($place->getPolygons() as $polygon) {
            $tempResults[] = $this->getCentralCoordinate($polygon->getCoordinates());
        }
        $centralCoordinate = $this->getCentralCoordinate($tempResults);

        $builder = new AddressBuilder($this->getName());
        $builder->setCoordinates($centralCoordinate->getLatitude(), $centralCoordinate->getLongitude());

        $builder->setAdminLevels($place->getAdminLevels()->all());
        $builder->setBounds(
            $place->getBounds()->getSouth(),
            $place->getBounds()->getWest(),
            $place->getBounds()->getNorth(),
            $place->getBounds()->getEast()
        );
        $builder->setStreetNumber($place->getStreetNumber());
        $builder->setStreetName($place->getStreetName());
        $builder->setPostalCode($place->getPostalCode());

        $builder->setLocality($place->getLocality());
        $builder->setSubLocality($place->getSubLocality());

        $builder->setCountry($place->getCountry()->getName());
        $builder->setCountryCode($place->getCountry()->getCode());
        $builder->setTimezone($place->getTimezone());

        return $builder->build();
    }

    /**
     * @param Coordinates $coordinates
     *
     * @return Place|null
     */
    private function findPlaceByCoordinates(Coordinates $coordinates)
    {
        $levels = $this->dataBase->getAdminLevels();

        /** @var Place|null $result */
        $result = null;
        foreach ($levels as $level) {
            $result ?
                $tempPlace = $result :
                $tempPlace = new Place (
                    $this->getName(),
                    new AdminLevelCollection([new AdminLevel($level, ',')])
                );

            $possiblePlaces = $this->dataBase->get($this->dataBase->compileKey($tempPlace, true, true, false));

            foreach ($possiblePlaces as $place) {
                foreach ($place->getPolygons() as $polygon) {
                    if ($this->checkCoordInBundle($coordinates->getLatitude(), $coordinates->getLongitude(), $polygon)) {
                        $result = $place;
                        continue 3;
                    }
                }
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
     * @return bool|int
     */
    private function isInPolygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)
    {
        $i = $j = $c = 0;
        for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
            if ( (($vertices_y[$i]  >  $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
                ($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) )
                $c = !$c;
        }
        return $c;
    }

    /**
     * @param Coordinates[] $coordinates
     *
     * @return Coordinates
     */
    private function getCentralCoordinate(array $coordinates): Coordinates
    {
        $total = count($coordinates);
        if ($total === 1) {
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
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'storageLocation';
    }
}
