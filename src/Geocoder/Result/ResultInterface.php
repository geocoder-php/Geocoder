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
    function getCoordinates();

    /**
     * Returns the latitude value.
     *
     * @return double
     */
    function getLatitude();

    /**
     * Returns the longitude value.
     *
     * @return double
     */
    function getLongitude();

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
    function getBounds();

    /**
     * Returns the street number value.
     *
     * @return string|int
     */
    function getStreetNumber();

    /**
     * Returns the street name value.
     *
     * @return string
     */
    function getStreetName();

    /**
     * Returns the city value.
     *
     * @return string
     */
    function getCity();

    /**
     * Returns the zipcode value.
     *
     * @return string
     */
    function getZipcode();

    /**
     * Returns the city district, or
     * sublocality, or neighborhood.
     *
     * @return string
     */
    function getCityDistrict();

    /**
     * Returns the county value.
     *
     * @return string
     */
    function getCounty();

    /**
     * Returns the region value.
     *
     * @return string
     */
    function getRegion();

    /**
     * Returns the region short name.
     *
     * @return string
     */
    function getRegionCode();

    /**
     * Returns the country value.
     *
     * @return string
     */
    function getCountry();

    /**
     * Returns the country ISO code.
     *
     * @return string
     */
    function getCountryCode();

    /**
     * Extracts data from an array.
     *
     * @param array $data An array.
     */
    function fromArray(array $data = array());

    /**
     * Returns an array with data indexed by name.
     *
     * @return array
     */
    function toArray();
}
