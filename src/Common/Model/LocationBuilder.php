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
 * A class that builds a Location or any of its subclasses.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class LocationBuilder
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
     * @var string|null
     */
    private $streetNumber;

    /**
     * @var string|null
     */
    private $streetName;

    /**
     * @var string|null
     */
    private $locality;

    /**
     * @var string|null
     */
    private $postalCode;

    /**
     * @var string|null
     */
    private $subLocality;

    /**
     * @var array
     */
    private $adminLevels = [];

    /**
     * @var string|null
     */
    private $country;

    /**
     * @var string|null
     */
    private $countryCode;

    /**
     * @var string|null
     */
    private $timezone;

    /**
     * @param string $class
     *
     * @return Location
     */
    public function build($class = Address::class)
    {
        if (!is_a($class, Location::class, true)) {
            throw new \LogicException('First parameter to LocationBuilder::build must be a class name implementing Geocoder\Location');
        }

        return new $class(
            $this->coordinates,
            $this->bounds,
            $this->streetNumber,
            $this->streetName,
            $this->postalCode,
            $this->locality,
            $this->subLocality,
            new AdminLevelCollection($this->adminLevels),
            new Country($this->country, $this->countryCode),
            $this->timezone
        );
    }

    /**
     * @param float $south
     * @param float $west
     * @param float $north
     * @param float $east
     *
     * @return LocationBuilder
     */
    public function setBounds($south, $west, $north, $east)
    {
        try {
            $this->bounds = new Bounds($south, $west, $north, $east);
        } catch (\InvalidArgumentException $e) {
            $this->bounds = null;
        }

        return $this;
    }

    /**
     * @param float $latitude
     * @param float $longitude
     *
     * @return LocationBuilder
     */
    public function setCoordinates($latitude, $longitude)
    {
        try {
            $this->coordinates = new Coordinates($latitude, $longitude);
        } catch (\InvalidArgumentException $e) {
            $this->coordinates = null;
        }

        return $this;
    }

    /**
     * @param int    $level
     * @param string $name
     * @param string $code
     *
     * @return LocationBuilder
     */
    public function addAdminLevel($level, $name, $code)
    {
        $this->adminLevels[] = new AdminLevel($level, $name, $code);

        return $this;
    }

    /**
     * @param null|string $streetNumber
     *
     * @return LocationBuilder
     */
    public function setStreetNumber($streetNumber)
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    /**
     * @param null|string $streetName
     *
     * @return LocationBuilder
     */
    public function setStreetName($streetName)
    {
        $this->streetName = $streetName;

        return $this;
    }

    /**
     * @param null|string $locality
     *
     * @return LocationBuilder
     */
    public function setLocality($locality)
    {
        $this->locality = $locality;

        return $this;
    }

    /**
     * @param null|string $postalCode
     *
     * @return LocationBuilder
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @param null|string $subLocality
     *
     * @return LocationBuilder
     */
    public function setSubLocality($subLocality)
    {
        $this->subLocality = $subLocality;

        return $this;
    }

    /**
     * @param array $adminLevels
     *
     * @return LocationBuilder
     */
    public function setAdminLevels($adminLevels)
    {
        $this->adminLevels = $adminLevels;

        return $this;
    }

    /**
     * @param null|string $country
     *
     * @return LocationBuilder
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @param null|string $countryCode
     *
     * @return LocationBuilder
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @param null|string $timezone
     *
     * @return LocationBuilder
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }
}
