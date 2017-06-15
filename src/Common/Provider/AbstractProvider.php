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

use Geocoder\Geocoder;
use Geocoder\Collection;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
abstract class AbstractProvider
{
    /**
     * Returns the default results.
     *
     * @return array
     *
     * @deprecated Use LocationBuilder
     */
    protected function getDefaults()
    {
        return [
            'latitude' => null,
            'longitude' => null,
            'bounds' => [
                'south' => null,
                'west' => null,
                'north' => null,
                'east' => null,
            ],
            'streetNumber' => null,
            'streetName' => null,
            'locality' => null,
            'postalCode' => null,
            'subLocality' => null,
            'adminLevels' => [],
            'country' => null,
            'countryCode' => null,
            'timezone' => null,
        ];
    }

    /**
     * Returns the results for the 'localhost' special case.
     *
     * @return array
     *
     * @deprecated
     */
    protected function getLocalhostDefaults()
    {
        return [
            'locality' => 'localhost',
            'country' => 'localhost',
        ];
    }
}
