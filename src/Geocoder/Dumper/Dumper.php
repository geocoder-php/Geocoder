<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Dumper;

use Geocoder\Model\Address;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface Dumper
{
    /**
     * Dumps an `Address` object as a string representation of
     * the implemented format.
     *
     * @param Address $address
     *
     * @return string
     */
    public function dump(Address $address);
}
