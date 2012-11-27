<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Formatter;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface FormatterInterface
{
    const STREET_NUMBER = '%n';

    const STREET_NAME   = '%S';

    const CITY          = '%L';

    const ZIPCODE       = '%z';

    const CITY_DISTRICT = '%D';

    const COUNTY        = '%P';

    const COUNTY_CODE   = '%p';

    const REGION        = '%R';

    const REGION_CODE   = '%r';

    const COUNTRY       = '%C';

    const COUNTRY_CODE  = '%c';

    const TIMEZONE      = '%T';

    /**
     * Format a ResultInterface object using a given format string.
     *
     * @param string $format
     *
     * @return string
     */
    public function format($format);
}
