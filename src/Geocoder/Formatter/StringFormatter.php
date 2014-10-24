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

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class StringFormatter
{
    const STREET_NUMBER = '%n';

    const STREET_NAME   = '%S';

    const LOCALITY      = '%L';

    const POSTAL_CODE   = '%z';

    const SUB_LOCALITY  = '%D';

    const COUNTY        = '%P';

    const COUNTY_CODE   = '%p';

    const REGION        = '%R';

    const REGION_CODE   = '%r';

    const COUNTRY       = '%C';

    const COUNTRY_CODE  = '%c';

    const TIMEZONE      = '%T';

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
        return strtr($format, array(
            self::STREET_NUMBER => $address->getStreetNumber(),
            self::STREET_NAME   => $address->getStreetName(),
            self::LOCALITY      => $address->getLocality(),
            self::POSTAL_CODE   => $address->getPostalCode(),
            self::SUB_LOCALITY  => $address->getSubLocality(),
            self::COUNTY        => $address->getCounty()->getName(),
            self::COUNTY_CODE   => $address->getCounty()->getCode(),
            self::REGION        => $address->getRegion()->getName(),
            self::REGION_CODE   => $address->getRegion()->getCode(),
            self::COUNTRY       => $address->getCountry()->getName(),
            self::COUNTRY_CODE  => $address->getCountry()->getCode(),
            self::TIMEZONE      => $address->getTimezone(),
        ));
    }
}
