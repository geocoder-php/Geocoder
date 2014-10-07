<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Dumper;

use Geocoder\Result\Address;

/**
 * @author Jan Sorgalla <jsorgalla@googlemail.com>
 */
class WktDumper implements Dumper
{
    /**
     * {@inheritDoc}
     */
    public function dump(Address $address)
    {
        return sprintf('POINT(%F %F)', $address->getLongitude(), $address->getLatitude());
    }
}
