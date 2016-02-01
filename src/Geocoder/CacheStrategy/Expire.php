<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\CacheStrategy;

use Psr\Cache\CacheItemPoolInterface;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class Expire implements Strategy
{
    private $cache;

    private $ttl;

    public function __construct(CacheItemPoolInterface $cache, $ttl = null)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function invoke($key, callable $function)
    {
        $item = $this->cache->getItem($key);

        if ($item->isHit()) {
            return $item->get();
        }

        $data = call_user_func($function);

        if ($this->ttl) {
            $item->expiresAfter($this->ttl);
        }

        $item->set($data);
        $this->cache->save($item);

        return $data;
    }
}
