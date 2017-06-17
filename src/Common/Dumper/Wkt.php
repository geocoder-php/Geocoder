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

/**
 * @author Jan Sorgalla <jsorgalla@googlemail.com>
 */
final class Wkt implements Dumper
{
    /**
     * {@inheritdoc}
     */
    public function dump(Location $location): string
    {
        $lat = null;
        $lon = null;
        if (null !== $coordinates = $location->getCoordinates()) {
            $lat = $coordinates->getLatitude();
            $lon = $coordinates->getLongitude();
        }

        return sprintf('POINT(%F %F)', $lon, $lat);
    }
}
