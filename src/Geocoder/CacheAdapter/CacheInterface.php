<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\CacheAdapter;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface CacheInterface
{
    /**
     * Stores a value with a unique key.
     *
     * @param string $key   A unique key.
     * @param \Geocoder\Result\ResultInterface  A result object.
     */
    function store($key, $value);

    /**
     * Retrieves a value identified by its key.
     *
     * @return \Geocoder\Result\ResultInterface A result object.
     */
    function retrieve($key);
}
