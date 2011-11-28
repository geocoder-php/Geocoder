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
class Memcached implements CacheInterface
{
    /**
     * Default Memcached server
     */
    const DEFAULT_SERVER    = '127.0.0.1';

    /**
     * Default Memcached port
     */
    const DEFAULT_PORT      = 11211;

    /**
     * @var \Memcached
     */
    protected $memcached = null;

    /**
     * @param string $server    The server address.
     * @param int $port         The server port.
     */
    public function __construct($server = self::DEFAULT_SERVER, $port = self::DEFAULT_PORT)
    {
        if (!class_exists('\Memcached')) {
            throw new \RuntimeException('You have to install Memcached and its PHP extension.');
        }

        $this->memcached = new \Memcached();
        $this->memcached->addServer($server, $port);
    }

    /**
     * {@inheritDoc}
     */
    public function store($key, $value)
    {
        $this->memcached->set(sha1($key), $value);
    }

    /**
     * {@inheritDoc}
     */
    public function retrieve($key)
    {
        return $this->memcached->get(sha1($key));
    }
}
