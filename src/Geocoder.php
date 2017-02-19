<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder;

use Geocoder\Provider\Provider;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface Geocoder extends Provider
{
    /**
     * Version
     */
    const VERSION = '4.0';

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
}
