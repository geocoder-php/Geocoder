<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Location;
use Geocoder\Model\Address;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
abstract class AbstractProvider implements Provider
{
    /**
     * Returns the results for the 'localhost' special case.
     *
     * @return Location
     */
    protected function getLocationForLocalhost(): Location
    {
        return Address::createFromArray([
            'providedBy' => $this->getName(),
            'locality' => 'localhost',
            'country' => 'localhost',
        ]);
    }
}
