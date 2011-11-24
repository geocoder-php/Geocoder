<?php

namespace Geocoder\CacheAdapter;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
 
class ApcCache implements CacheInterface {

    public function __construct()
    {
        if ( ! extension_loaded('apc') ) {
            throw new \RuntimeException('Apc extension must be loaded');
        }
    }

    /**
     * Stores a value with a unique key.
     *
     * @param string $key   A unique key.
     * @param \Geocoder\Result\ResultInterface  A result object.
     */
    public function store($key, $value)
    {
        $retval = apc_store($key, $value, 3600);
        return $retval;
    }

    /**
     * Retrieves a value identified by its key.
     *
     * @return \Geocoder\Result\ResultInterface A result object.
     */
    public function retrieve($key)
    {
        $value = apc_fetch($key);
        return false === $value ? null : $value;
    }


}
