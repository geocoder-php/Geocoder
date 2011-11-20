<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Cache;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class InMemory implements CacheInterface
{
    /**
     * @var array
     */
    protected $store = array();

    /**
     * {@inheritDoc}
     */
    public function store($key, $value)
    {
        if (!isset($this->store[$key])) {
            $this->store[$key] = $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function retrieve($key)
    {
        return isset($this->store[$key]) ? $this->store[$key] : null;
    }
}
