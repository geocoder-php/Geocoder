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
class Memcache implements CacheInterface
{
    /**
     * @var \Memcache
     */
    protected $adapter;

    public function __construct(\Memcache $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Stores a value with a unique key.
     *
     * @param string $key   A unique key.
     * @param \Geocoder\Result\ResultInterface  A result object.
     */
    public function store($key, $value)
    {
        $this->adapter->set($key, serialize($value));
    }

    /**
     * Retrieves a value identified by its key.
     *
     * @return \Geocoder\Result\ResultInterface A result object.
     */
    public function retrieve($key)
    {
        $value = $this->adapter->get($key);

        return $value ? unserialize($value) : null;
    }
}
