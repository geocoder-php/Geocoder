<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Dumper;

use Geocoder\Location;

/**
 * @author Tomas Norkūnas <norkunas.tom@gmail.com>
 */
class GeoArray implements Dumper
{
    /**
     * {@inheritdoc}
     */
    public function dump(Location $location)
    {
        $properties = array_filter($location->toArray(), function ($value) {
            return !empty($value);
        });

        unset(
            $properties['latitude'],
            $properties['longitude'],
            $properties['bounds']
        );

        if (0 === count($properties)) {
            $properties = null;
        }

        $lat = 0;
        $lon = 0;
        if (null !== $coordinates = $location->getCoordinates()) {
            $lat = $coordinates->getLatitude();
            $lon = $coordinates->getLongitude();
        }

        $array = [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$lon, $lat],
            ],
            'properties' => $properties,
        ];

        if (null !== $bounds = $location->getBounds()) {
            $array['bounds'] = $bounds->toArray();
        }

        return $array;
    }
}
