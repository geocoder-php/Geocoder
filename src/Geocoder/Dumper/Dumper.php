<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Dumper;

use Geocoder\Location;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface Dumper
{
    /**
     * Dumps an `Location` object as a string representation of
     * the implemented format.
     *
     * @param Location $location
     *
     * @return string
     */
    public function dump(Location $location);
}
