<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface Geocoder
{
    /**
     * Version
     */
    const VERSION = '3.3.1-dev';

    /**
     * Geocodes a given value.
     *
     * @param string $value
     *
     * @return Collection
     * @throws \Geocoder\Exception\Exception
     */
    public function geocode($value);

    /**
     * Reverses geocode given latitude and longitude values.
     *
     * @param double $latitude
     * @param double $longitude
     *
     * @return Collection
     * @throws \Geocoder\Exception\Exception
     */
    public function reverse($latitude, $longitude);

    /**
     * Returns the maximum number of Address objects that can be
     * returned by `geocode()` or `reverse()` methods.
     *
     * @return integer
     */
    public function getLimit();

    /**
     * Sets the maximum number of `Address` objects that can be
     * returned by `geocode()` or `reverse()` methods.
     *
     * @param integer $limit
     *
     * @return Geocoder
     */
    public function limit($limit);
}
