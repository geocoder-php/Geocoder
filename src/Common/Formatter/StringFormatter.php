<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Formatter;

use Geocoder\Location;
use Geocoder\Model\AdminLevelCollection;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class StringFormatter
{
    public const STREET_NUMBER = '%n';

    public const STREET_NAME = '%S';

    public const LOCALITY = '%L';

    public const POSTAL_CODE = '%z';

    public const SUB_LOCALITY = '%D';

    public const ADMIN_LEVEL = '%A';

    public const ADMIN_LEVEL_CODE = '%a';

    public const COUNTRY = '%C';

    public const COUNTRY_CODE = '%c';

    public const TIMEZONE = '%T';

    /**
     * Transform an `Address` instance into a string representation.
     */
    public function format(Location $location, string $format): string
    {
        $countryName = null;
        $code = null;
        if (null !== $country = $location->getCountry()) {
            $countryName = $country->getName();
            if (null !== $code = $country->getCode()) {
                $code = strtoupper($code);
            }
        }

        $replace = [
            self::STREET_NUMBER => $location->getStreetNumber(),
            self::STREET_NAME => $location->getStreetName(),
            self::LOCALITY => $location->getLocality(),
            self::POSTAL_CODE => $location->getPostalCode(),
            self::SUB_LOCALITY => $location->getSubLocality(),
            self::COUNTRY => $countryName,
            self::COUNTRY_CODE => $code,
            self::TIMEZONE => $location->getTimezone(),
        ];

        for ($level = 1; $level <= AdminLevelCollection::MAX_LEVEL_DEPTH; ++$level) {
            $replace[self::ADMIN_LEVEL.$level] = null;
            $replace[self::ADMIN_LEVEL_CODE.$level] = null;
        }

        foreach ($location->getAdminLevels() as $level => $adminLevel) {
            $replace[self::ADMIN_LEVEL.$level] = $adminLevel->getName();
            $replace[self::ADMIN_LEVEL_CODE.$level] = $adminLevel->getCode();
        }

        return strtr($format, $replace);
    }
}
