<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Model;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class Address
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
     * @param string $streetNumber
     * @param string $streetName
     * @param string $postalCode
     * @param string $locality
     * @param string $subLocality
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
     * Returns an array of coordinates (latitude, longitude).
     *
     * @return Coordinates
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * Returns the latitude value.
     *
     * @return double
     */
    public function getLatitude()
    {
        if (null === $this->coordinates) {
            return null;
        }

        return $this->coordinates->getLatitude();
    }

    /**
     * Returns the longitude value.
     *
     * @return double
     */
    public function getLongitude()
    {
        if (null === $this->coordinates) {
            return null;
        }

        return $this->coordinates->getLongitude();
    }

    /**
     * Returns the bounds value.
     *
     * @return Bounds
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
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Returns the country ISO code.
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country->getCode();
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

        return array(
            'latitude'     => $this->getLatitude(),
            'longitude'    => $this->getLongitude(),
            'bounds'       => $this->bounds->toArray(),
            'streetNumber' => $this->streetNumber,
            'streetName'   => $this->streetName,
            'postalCode'   => $this->postalCode,
            'locality'     => $this->locality,
            'subLocality'  => $this->subLocality,
            'adminLevels'  => $adminLevels,
            'country'      => $this->country->getName(),
            'countryCode'  => $this->country->getCode(),
            'timezone'     => $this->timezone,
        );
    }
}
