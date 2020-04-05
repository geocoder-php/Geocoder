<?php
declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */
namespace Geocoder\Provider\StorageLocation\Model;

use Geocoder\Model\Address;
use Geocoder\Model\AdminLevelCollection;
use Geocoder\Model\Bounds;
use Geocoder\Model\Coordinates;
use Geocoder\Model\Country;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
class Place extends Address
{
    /**
     * @var Polygon[]|null
     */
    private $polygons;

    /**
     * @param string               $providedBy
     * @param AdminLevelCollection $adminLevels
     * @param Coordinates|null     $coordinates
     * @param Bounds|null          $bounds
     * @param string|null          $streetNumber
     * @param string|null          $streetName
     * @param string|null          $postalCode
     * @param string|null          $locality
     * @param string|null          $subLocality
     * @param Country|null         $country
     * @param string|null          $timezone
     * @param Polygon[]|null       $polygons
     */
    public function __construct(
        string $providedBy,
        AdminLevelCollection $adminLevels,
        $coordinates = null,
        Bounds $bounds = null,
        string $streetNumber = null,
        string $streetName = null,
        string $postalCode = null,
        string $locality = null,
        string $subLocality = null,
        Country $country = null,
        string $timezone = null,
        array $polygons = null
    ) {
        parent::__construct(
            $providedBy,
            $adminLevels,
            $coordinates,
            $bounds,
            $streetNumber,
            $streetName,
            $postalCode,
            $locality,
            $subLocality,
            $country,
            $timezone
        );

        $this->polygons = $polygons;
    }

    /**
     * @param Polygon[] $polygons
     *
     * @return Place
     */
    public function setPolygons(array $polygons)
    {
        $this->polygons = $polygons;

        return $this;
    }

    public function setPolygonsFromArray(array $rawPolygons): self
    {
        foreach ($rawPolygons as $rawPolygon) {
            $tempPolygon = new Polygon();
            foreach ($rawPolygon as $coordinate) {
                if (isset($coordinate[1]) && isset($coordinate[0])) {
                    $tempPolygon->addCoordinates(new Coordinates($coordinate[1], $coordinate[0]));
                }
            }
            $this->polygons[] = $tempPolygon;
        }

        return $this;
    }

    /**
     * @return Polygon[]
     */
    public function getPolygons()
    {
        return $this->polygons;
    }

    public function getPolygonsAsArray()
    {
        $result = [];
        if (is_array($this->polygons)) {
            foreach ($this->polygons as $key => $polygon) {
                $result[$key] = $polygon->toArray();
            }
        }
        return $result;
    }

    public static function createFromArray(array $data)
    {
        /** @var Place $result */
        $result = parent::createFromArray($data);
        if (isset($data['polygons'])) {
            $result->setPolygonsFromArray($data['polygons']);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        $parentResult = parent::toArray();
        $parentResult['polygons'] = $this->getPolygonsAsArray();

        return $parentResult;
    }
}
