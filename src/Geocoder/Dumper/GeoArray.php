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
class GeoArray implements Dumper
{
    /**
     * {@inheritDoc}
     */
    public function dump(Address $address)
    {
        $properties = array_filter($address->toArray(), function ($value) {
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

        $array = [
            'type' => 'Feature',
            'geometry' => [
                'type'          => 'Point',
                'coordinates'   => [ $address->getLongitude(), $address->getLatitude() ]
            ],
            'properties' => $properties,
        ];

        if (null !== $bounds = $address->getBounds()) {
            if ($bounds->isDefined()) {
                $array['bounds'] = $bounds->toArray();
            }
        }

        return $array;
    }
}
