<?php

/**
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
final class Address implements Location
{
    /**
     * @var Coordinates
     */
    private $coordinates;

    /**
     * @var Bounds
     */
    private $bounds;

    /**
     * @var string|int
     */
    private $streetNumber;

    /**
     * @var string
     */
    private $streetName;

    /**
     * @var string
     */
    private $subLocality;

    /**
     * @var string
     */
    private $locality;

    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var AdminLevelCollection
     */
    private $adminLevels;

    /**
     * @var Country
     */
    private $country;

    /**
     * @var string
     */
    private $timezone;

    /**
     *
     * @param Coordinates|null $coordinates
     * @param Bounds|null $bounds
     * @param string|null $streetNumber
     * @param string|null $streetName
     * @param string|null $postalCode
     * @param string|null $locality
     * @param string|null $subLocality
     * @param AdminLevelCollection|null $adminLevels
     * @param Country|null $country
     * @param string|null $timezone
     */
    public function __construct(
        Coordinates $coordinates          = null,
        Bounds $bounds                    = null,
        $streetNumber                     = null,
        $streetName                       = null,
        $postalCode                       = null,
        $locality                         = null,
        $subLocality                      = null,
        AdminLevelCollection $adminLevels = null,
        Country $country                  = null,
        $timezone                         = null
    ) {
        $this->coordinates  = $coordinates;
        $this->bounds       = $bounds;
        $this->streetNumber = $streetNumber;
        $this->streetName   = $streetName;
        $this->postalCode   = $postalCode;
        $this->locality     = $locality;
        $this->subLocality  = $subLocality;
        $this->adminLevels  = $adminLevels ?: new AdminLevelCollection();
        $this->country      = $country;
        $this->timezone     = $timezone;
    }

    /**
     * Returns the coordinates for this address.
     *
     * @return Coordinates|null
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * Returns the bounds.
     *
     * @return Bounds|null
     */
    public function getBounds()
    {
        return $this->bounds;
    }

    /**
     * Returns the street number value.
     *
     * @return string|int
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }

    /**
     * Returns the street name value.
     *
     * @return string
     */
    public function getStreetName()
    {
        return $this->streetName;
    }

    /**
     * Returns the city or locality value.
     *
     * @return string
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * Returns the postal code or zipcode value.
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Returns the locality district, or
     * sublocality, or neighborhood.
     *
     * @return string
     */
    public function getSubLocality()
    {
        return $this->subLocality;
    }

    /**
     * Returns the administrative levels.
     *
     * @return AdminLevelCollection
     */
    public function getAdminLevels()
    {
        return $this->adminLevels;
    }

    /**
     * Returns the country value.
     *
     * @return Country|null
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Returns the timezone.
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Returns an array with data indexed by name.
     *
     * @return array
     */
    public function toArray()
    {
        $adminLevels = [];
        foreach ($this->adminLevels as $adminLevel) {
            $adminLevels[$adminLevel->getLevel()] = [
                'name'  => $adminLevel->getName(),
                'code'  => $adminLevel->getCode()
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
            'west'  => null,
            'north' => null,
            'east'  => null,
        ];

        return array(
            'latitude'     => $lat,
            'longitude'    => $lon,
            'bounds'       => null !== $this->bounds ? $this->bounds->toArray() : $noBounds,
            'streetNumber' => $this->streetNumber,
            'streetName'   => $this->streetName,
            'postalCode'   => $this->postalCode,
            'locality'     => $this->locality,
            'subLocality'  => $this->subLocality,
            'adminLevels'  => $adminLevels,
            'country'      => $countryName,
            'countryCode'  => $countryCode,
            'timezone'     => $this->timezone,
        );
    }
}
