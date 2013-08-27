<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\HttpAdapter\HttpAdapterInterface;

/**
 * @author Niklas Närhinen <niklas@narhinen.net>
 */
class OpenStreetMapsProvider extends NominatimProvider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://nominatim.openstreetmap.org/search?q=%s&format=xml&addressdetails=1&limit=%d';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://nominatim.openstreetmap.org/reverse?format=xml&lat=%F&lon=%F&addressdetails=1&zoom=18';

    /**
     * @var string
     */
    const ROOT_URL = 'http://nominatim.openstreetmap.org';

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $locale  A locale (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $locale = null)
    {
        parent::__construct($adapter, static::ROOT_URL, $locale);
    }
}
