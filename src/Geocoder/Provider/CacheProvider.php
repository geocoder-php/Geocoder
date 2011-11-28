<?php

namespace Geocoder\Provider;

use Geocoder\CacheAdapter\CacheInterface;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class CacheProvider implements ProviderInterface
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var ProviderInterface
     */
    protected $provider;

    public function __construct(CacheInterface $cache, ProviderInterface $provider)
    {
        $this->cache = $cache;
        $this->provider = $provider;
    }

    /**
     * Returns an associative array with data treated by the provider.
     *
     * @param string $address   An address (IP or street).
     * @return array
     */
    public function getGeocodedData($address)
    {
        if (null !== $result = $this->cache->retrieve($address)) {
            return $result;
        }

        $result = $this->provider->getGeocodedData($address);
        $this->cache->store($address, $result);
        return $result;
    }

    /**
     * Returns an associative array with data treated by the provider.
     *
     * @param array $coordinates    Coordinates (latitude, longitude).
     * @return array
     */
    public function getReversedData(array $coordinates)
    {
        $key = sha1(serialize($coordinates));
        if (null !== $result = $this->cache->retrieve($key)) {
            return $result;
        }

        $result = $this->provider->getReversedData($coordinates);
        $this->cache->store($key, $result);
        return $result;
    }

    /**
     * Returns the provider's name.
     *
     * @return string
     */
    public function getName()
    {
        return 'cache';
    }
}
