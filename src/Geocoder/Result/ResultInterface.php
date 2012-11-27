<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Result;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface ResultInterface
{
    /**
     * Returns an array of coordinates (latitude, longitude).
     *
     * @return array
     */
    public function getCoordinates();

    /**
     * Returns the latitude value.
     *
     * @return double
     */
    public function getLatitude();

    /**
     * Returns the longitude value.
     *
     * @return double
     */
    public function getLongitude();

    /**
     * Returns the bounds value.
     *
     * Bounds format:
     *
     * array(
     *     'south' => (double)
     *     'west'  => (double)
     *     'north' => (double)
     *     'east'  => (double)
     * )
     *
     * @return array
     */
    public function getBounds();

    /**
     * Returns the street number value.
     *
     * @return string|int
     */
    public function getStreetNumber();

    /**
     * Returns the street name value.
     *
     * @return string
     */
    public function getStreetName();

    /**
     * Returns the city value.
     *
     * @return string
     */
    public function getCity();

    /**
     * Returns the zipcode value.
     *
     * @return string
     */
    public function getZipcode();

    /**
     * Returns the city district, or
     * sublocality, or neighborhood.
     *
     * @return string
     */
    public function getCityDistrict();

    /**
     * Returns the county value.
     *
     * @return string
     */
    public function getCounty();

    /**
     * Returns the county short name.
     *
     * @return string
     */
    public function getCountyCode();

    /**
     * Returns the region value.
     *
     * @return string
     */
    public function getRegion();

    /**
     * Returns the region short name.
     *
     * @return string
     */
    public function getRegionCode();

    /**
     * Returns the country value.
     *
     * @return string
     */
    public function getCountry();

    /**
     * Returns the country ISO code.
     *
     * @return string
     */
    public function getCountryCode();

    /**
     * Returns the timezone.
     *
     * @return string
     */
    public function getTimezone();

    /**
     * Extracts data from an array.
     *
     * @param array $data An array.
     */
    public function fromArray(array $data = array());

    /**
     * Returns an array with data indexed by name.
     *
     * @return array
     */
    public function toArray();
}
