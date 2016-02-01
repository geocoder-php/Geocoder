<?php

namespace Geocoder\CacheStrategy;

use Psr\Cache\CacheItemPoolInterface;

class StaleIfError implements Strategy
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

        try {
            $data = call_user_func($function);
        } catch (\Exception $e) {
            if (!$item->isHit()) {
                throw $e;
            }

            return $item->get();
        }

        $item->set($data);

        if ($this->ttl) {
            $item->expiresAfter($this->ttl);
        }

        $this->cache->save($item);

        return $data;
    }
}