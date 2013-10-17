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
 * @author William Durand <william.durand1@gmail.com>
 */
class OpenStreetMapsProvider extends OpenStreetMapProvider
{
    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $locale  A locale (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $locale = null)
    {
        parent::__construct($adapter, static::ROOT_URL, $locale);

        trigger_error('The `OpenStreetMapsProvider` is deprecated and will be removed. Use the `OpenStreetMapProvider` instead.', E_USER_DEPRECATED);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'openstreetmaps';
    }
}
