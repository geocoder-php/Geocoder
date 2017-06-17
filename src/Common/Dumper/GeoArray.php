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
 * @author Tomas Norkūnas <norkunas.tom@gmail.com>
 */
final class GeoArray extends AbstractArrayDumper implements Dumper
{
    /**
     * {@inheritdoc}
     */
    public function dump(Location $location): array
    {
        return $this->getArray($location);
    }
}
