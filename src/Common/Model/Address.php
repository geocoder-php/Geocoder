<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Model;

use Geocoder\Location;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class Address implements Location
{
    /**
     * @var Coordinates|null
     */
    private $coordinates;

    /**
     * @var Bounds|null
     */
    private $bounds;

    /**
     * @var string|int|null
     */
    private $streetNumber;

    /**
     * @var string|null
     */
    private $streetName;

    /**
     * @var string|null
     */
    private $subLocality;

    /**
     * @var string|null
     */
    private $locality;

    /**
     * @var string|null
     */
    private $postalCode;

    /**
     * @var AdminLevelCollection
     */
    private $adminLevels;

    /**
     * @var Country|null
     */
    private $country;

    /**
     * @var string|null
     */
    private $timezone;

    /**
     * @var string
     */
    private $providedBy;

    final public function __construct(
        string $providedBy,
        AdminLevelCollection $adminLevels,
        ?Coordinates $coordinates = null,
        ?Bounds $bounds = null,
        ?string $streetNumber = null,
        ?string $streetName = null,
        ?string $postalCode = null,
        ?string $locality = null,
        ?string $subLocality = null,
        ?Country $country = null,
        ?string $timezone = null,
    ) {
        $this->providedBy = $providedBy;
        $this->adminLevels = $adminLevels;
        $this->coordinates = $coordinates;
        $this->bounds = $bounds;
        $this->streetNumber = $streetNumber;
        $this->streetName = $streetName;
        $this->postalCode = $postalCode;
        $this->locality = $locality;
        $this->subLocality = $subLocality;
        $this->country = $country;
        $this->timezone = $timezone;
    }

    public function getProvidedBy(): string
    {
        return $this->providedBy;
    }

    public function getCoordinates(): ?Coordinates
    {
        return $this->coordinates;
    }

    public function getBounds(): ?Bounds
    {
        return $this->bounds;
    }

    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function getLocality(): ?string
    {
        return $this->locality;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getSubLocality(): ?string
    {
        return $this->subLocality;
    }

    public function getAdminLevels(): AdminLevelCollection
    {
        return $this->adminLevels;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    /**
     * Create an Address with an array. Useful for testing.
     *
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public static function createFromArray(array $data)
    {
        $defaults = [
            'providedBy' => 'n/a',
            'latitude' => null,
            'longitude' => null,
            'bounds' => [
                'south' => null,
                'west' => null,
                'north' => null,
                'east' => null,
            ],
            'streetNumber' => null,
            'streetName' => null,
            'locality' => null,
            'postalCode' => null,
            'subLocality' => null,
            'adminLevels' => [],
            'country' => null,
            'countryCode' => null,
            'timezone' => null,
        ];

        $data = array_merge($defaults, $data);

        $adminLevels = [];
        foreach ($data['adminLevels'] as $adminLevel) {
            if (null === $adminLevel['level'] || 0 === $adminLevel['level']) {
                continue;
            }

            $name = $adminLevel['name'] ?? $adminLevel['code'] ?? null;
            if (null === $name || '' === $name) {
                continue;
            }

            $adminLevels[] = new AdminLevel($adminLevel['level'], $name, $adminLevel['code'] ?? null);
        }

        return new static(
            $data['providedBy'],
            new AdminLevelCollection($adminLevels),
            self::createCoordinates(
                $data['latitude'],
                $data['longitude']
            ),
            self::createBounds(
                $data['bounds']['south'],
                $data['bounds']['west'],
                $data['bounds']['north'],
                $data['bounds']['east']
            ),
            $data['streetNumber'],
            $data['streetName'],
            $data['postalCode'],
            $data['locality'],
            $data['subLocality'],
            self::createCountry($data['country'], $data['countryCode']),
            $data['timezone']
        );
    }

    /**
     * @param float $latitude
     * @param float $longitude
     *
     * @return Coordinates|null
     */
    private static function createCoordinates($latitude, $longitude)
    {
        if (null === $latitude || null === $longitude) {
            return null;
        }

        return new Coordinates($latitude, $longitude);
    }

    /**
     * @param string|null $name
     * @param string|null $code
     *
     * @return Country|null
     */
    private static function createCountry($name, $code)
    {
        if (null === $name && null === $code) {
            return null;
        }

        return new Country($name, $code);
    }

    /**
     * @return Bounds|null
     */
    private static function createBounds(?float $south, ?float $west, ?float $north, ?float $east)
    {
        if (null === $south || null === $west || null === $north || null === $east) {
            return null;
        }

        return new Bounds($south, $west, $north, $east);
    }

    public function toArray(): array
    {
        $adminLevels = [];
        foreach ($this->adminLevels as $adminLevel) {
            $adminLevels[$adminLevel->getLevel()] = [
                'name' => $adminLevel->getName(),
                'code' => $adminLevel->getCode(),
                'level' => $adminLevel->getLevel(),
            ];
        }

        $lat = null;
        $lon = null;
        if (null !== $coordinates = $this->getCoordinates()) {
            $lat = $coordinates->getLatitude();
            $lon = $coordinates->getLongitude();
        }

        $countryName = null;
        $countryCode = null;
        if (null !== $country = $this->getCountry()) {
            $countryName = $country->getName();
            $countryCode = $country->getCode();
        }

        $noBounds = [
            'south' => null,
            'west' => null,
            'north' => null,
            'east' => null,
        ];

        return [
            'providedBy' => $this->providedBy,
            'latitude' => $lat,
            'longitude' => $lon,
            'bounds' => null !== $this->bounds ? $this->bounds->toArray() : $noBounds,
            'streetNumber' => $this->streetNumber,
            'streetName' => $this->streetName,
            'postalCode' => $this->postalCode,
            'locality' => $this->locality,
            'subLocality' => $this->subLocality,
            'adminLevels' => $adminLevels,
            'country' => $countryName,
            'countryCode' => $countryCode,
            'timezone' => $this->timezone,
        ];
    }
}
