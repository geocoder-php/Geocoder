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
use Geocoder\Model\AdminLevel;
use Geocoder\Model\Coordinates;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
class Place
{
    const DEFAULT_LOCALE = 'en';

    /**
     * @var Polygon[]|null
     */
    private $polygons;

    /**
     * @var Address[]
     */
    private $addresses = [];

    /**
     * Current selected locale
     *
     * @var string
     */
    private $currentLocale;

    /**
     * Unique id of Place object
     *
     * @var string
     */
    private $objectHash;

    /**
     * @param Address|Address[]    $address
     * @param Polygon[]|null       $polygons
     * @param string               $locale
     */
    public function __construct(
        $address,
        array $polygons = null,
        string $locale = self::DEFAULT_LOCALE
    ) {
        if (is_array($address)) {
            foreach ($address as $localeNode => $addressNode) {
                $this->addresses[$localeNode] = $addressNode;
            }
        } else {
            $this->addresses[$locale] = $address;
        }

        $this->polygons = $polygons;
        $this->currentLocale = $locale;
    }

    /**
     * Return Address object in selected locale
     *
     * @return Address
     */
    public function getSelectedAddress(): Address
    {
        return $this->addresses[$this->currentLocale];
    }

    /**
     * Set Address for selected locale
     *
     * @param Address $address
     *
     * @return bool
     */
    public function setSelectedAddress(Address $address): bool
    {
        $this->addresses[$this->currentLocale] = $address;

        return true;
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    public function selectLocale(string $locale): bool
    {
        $this->currentLocale = $locale;

        return true;
    }

    /**
     * Return associated array with available Address object and locales as keys
     *
     * @return Address[]
     */
    public function getAvailableAddresses(): array
    {
        return $this->addresses;
    }

    /**
     * Returning maximum admin level for entity
     *
     * @return int
     */
    public function getMaxAdminLevel(): int
    {
        $address = $this->getSelectedAddress();

        $max = 0;
        /** @var AdminLevel $level */
        foreach ($address->getAdminLevels() as $level) {
            if ($level->getLevel() > $max) {
                $max = $level->getLevel();
            }
        }

        return $max;
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

    /**
     * @param array $rawPolygons
     *
     * @return $this
     */
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

    /**
     * @param array $data
     * @param array $includeLocales
     *
     * @return Place
     */
    public static function createFromArray(array $data, array $includeLocales = [])
    {
        $addresses = [];
        $firstLocale = '';
        if (isset($data['address']) && is_array($data['address'])) {
            count($includeLocales) > 0
                ? $preparedData = array_intersect_key($data['address'], array_fill_keys($includeLocales, true))
                : $preparedData = $data['address'];

            foreach ($preparedData as $locale => $rawAddress) {
                if ($firstLocale === '') {
                    $firstLocale = $locale;
                }

                $addresses[$locale] = Address::createFromArray($rawAddress);
            }
        }


        $place = new Place($addresses, null, $firstLocale);

        if (isset($data['polygons'])) {
            $place->setPolygonsFromArray($data['polygons']);
        }

        if (isset($data['hash'])) {
            $place->setObjectHash($data['hash']);
        }

        return $place;
    }

    /**
     * @return string
     */
    public function getObjectHash(): string
    {
        return $this->objectHash;
    }

    /**
     * @param string $objectHash
     *
     * @return Place
     */
    public function setObjectHash(string $objectHash): self
    {
        $this->objectHash = $objectHash;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->addresses as $locale => $address) {
            $result['address'][$locale] = $address->toArray();
        }

        $result['polygons'] = $this->getPolygonsAsArray();
        $result['hash'] = $this->objectHash;

        return $result;
    }
}
