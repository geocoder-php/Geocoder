<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Http\Client\HttpClient;

/**
 * @author Niklas Närhinen <niklas@narhinen.net>
 */
class OpenStreetMap extends Nominatim
{
    /**
     * @var string
     */
    const ROOT_URL = 'http://nominatim.openstreetmap.org';

    /**
     * @param HttpClient $client An HTTP adapter.
     * @param string               $locale  A locale (optional).
     */
    public function __construct(HttpClient $client, $locale = null)
    {
        parent::__construct($client, static::ROOT_URL, $locale);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'openstreetmap';
    }
}
