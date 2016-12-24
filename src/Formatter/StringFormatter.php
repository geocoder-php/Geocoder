<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Formatter;

use Geocoder\Model\AdminLevelCollection;
use Geocoder\Location;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class StringFormatter
{
    const STREET_NUMBER    = '%n';

    const STREET_NAME      = '%S';

    const LOCALITY         = '%L';

    const POSTAL_CODE      = '%z';

    const SUB_LOCALITY     = '%D';

    const ADMIN_LEVEL      = '%A';

    const ADMIN_LEVEL_CODE = '%a';

    const COUNTRY          = '%C';

    const COUNTRY_CODE     = '%c';

    const TIMEZONE         = '%T';

    /**
     * Transform an `Address` instance into a string representation.
     *
     * @param Location $location
     * @param string $format
     *
     * @return string
     */
    public function format(Location $location, $format)
    {
        $replace = [
            self::STREET_NUMBER => $location->getStreetNumber(),
            self::STREET_NAME   => $location->getStreetName(),
            self::LOCALITY      => $location->getLocality(),
            self::POSTAL_CODE   => $location->getPostalCode(),
            self::SUB_LOCALITY  => $location->getSubLocality(),
            self::COUNTRY       => $location->getCountry()->getName(),
            self::COUNTRY_CODE  => $location->getCountry()->getCode(),
            self::TIMEZONE      => $location->getTimezone(),
        ];

        for ($level = 1; $level <= AdminLevelCollection::MAX_LEVEL_DEPTH; $level ++) {
            $replace[self::ADMIN_LEVEL . $level] = null;
            $replace[self::ADMIN_LEVEL_CODE . $level] = null;
        }

        foreach ($location->getAdminLevels() as $level => $adminLevel) {
            $replace[self::ADMIN_LEVEL . $level] = $adminLevel->getName();
            $replace[self::ADMIN_LEVEL_CODE . $level] = $adminLevel->getCode();
        }

        return strtr($format, $replace);
    }
}
