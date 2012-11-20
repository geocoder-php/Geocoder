<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder;

use Geocoder\Result\Geocoded;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface GeocoderInterface
{
    /**
     * Geocode a given value.
     *
     * @param string $value A value to geocode.
     *
     * @return Geocoded A Geocoded result object.
     */
    public function geocode($value);

    /**
     * Reverse geocode given latitude and longitude values.
     *
     * @param double $latitude  Latitude.
     * @param double $longitude Longitude.
     *
     * @return Geocoded A Geocoded result object.
     */
    public function reverse($latitude, $longitude);
}
