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
 * @author Jan Sorgalla <jsorgalla@googlemail.com>
 */
class Wkb implements Dumper
{
    /**
     * {@inheritDoc}
     */
    public function dump(Address $address)
    {
        return pack('cLdd', 1, 1, $address->getLongitude(), $address->getLatitude());
    }
}
