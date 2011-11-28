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
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class Apc implements CacheInterface
{
    public function __construct()
    {
        if (!extension_loaded('apc')) {
            throw new \RuntimeException('Apc extension must be loaded');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function store($key, $value)
    {
        $retval = apc_store($key, $value);

        return $retval;
    }

    /**
     * {@inheritDoc}
     */
    public function retrieve($key)
    {
        $value = apc_fetch($key);

        return false === $value ? null : $value;
    }
}
