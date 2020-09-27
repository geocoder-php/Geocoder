<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Dumper;

use Geocoder\Location;

abstract class AbstractDumper
{
    protected function formatName(Location $address): string
    {
        $name = [];
        $array = $address->toArray();

        foreach (['streetNumber', 'streetName', 'postalCode', 'locality'] as $attr) {
            $name[] = $array[$attr];
        }

        if (isset($array['adminLevels'][2])) {
            $name[] = $array['adminLevels'][2]['name'];
        }

        if (isset($array['adminLevels'][1])) {
            $name[] = $array['adminLevels'][1]['name'];
        }

        $name[] = $array['country'];

        return implode(', ', array_filter($name));
    }
}
