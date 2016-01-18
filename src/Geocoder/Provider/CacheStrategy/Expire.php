<?php

namespace Geocoder\Provider\CacheStrategy;

class Expire implements Stragegy
{
    private $cache;

    private $ttl;

    public function __construct(CacheItemPoolInterface $cache, $ttl = null)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function invoke($key, callable $function, CacheItemPoolInterface $cache)
    {
        $key = $this->generateKey($address);
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
