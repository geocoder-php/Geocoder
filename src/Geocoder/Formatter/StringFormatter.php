<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Formatter;

use Geocoder\Model\Address;
use Geocoder\Model\AdminLevel;
use Geocoder\Model\AdminLevelCollection;

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
     * @param Address $address
     * @param string  $format
     *
     * @return string
     */
    public function format(Address $address, $format)
    {
        $replace = [
            self::STREET_NUMBER => $address->getStreetNumber(),
            self::STREET_NAME   => $address->getStreetName(),
            self::LOCALITY      => $address->getLocality(),
            self::POSTAL_CODE   => $address->getPostalCode(),
            self::SUB_LOCALITY  => $address->getSubLocality(),
            self::COUNTRY       => $address->getCountry()->getName(),
            self::COUNTRY_CODE  => $address->getCountry()->getCode(),
            self::TIMEZONE      => $address->getTimezone(),
        ];

        for ($level = 1; $level <= AdminLevelCollection::MAX_LEVEL_DEPTH; $level ++) {
            $replace[self::ADMIN_LEVEL . $level] = null;
            $replace[self::ADMIN_LEVEL_CODE . $level] = null;
        }

        foreach ($address->getAdminLevels() as $level => $adminLevel) {
            $replace[self::ADMIN_LEVEL . $level] = $adminLevel->getName();
            $replace[self::ADMIN_LEVEL_CODE . $level] = $adminLevel->getCode();
        }

        return strtr($format, $replace);
    }
}
