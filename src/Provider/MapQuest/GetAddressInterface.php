<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\MapQuest;

use Geocoder\Location;

interface GetAddressInterface
{
    /**
     * @return Location|null
     */
    public function getAddress();
}
