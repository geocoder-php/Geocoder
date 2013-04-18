<?php

namespace Geocoder\Provider;

use Doctrine\Common\Cache\Cache;

use Geocoder\Exception\InvalidCredentialsException;
use Geocoder\Exception\NoResultException;
use Geocoder\Exception\UnsupportedException;

class CacheProvider implements ProviderInterface
{
    public $cachePrefix = 'geocoder_geocode_';
    public $cacheReversePrefix = 'geocoder_reverse_';

    protected $cache;
    protected $provider;

    public function __construct(Cache $cache, ProviderInterface $provider)
    {
        $this->cache = $cache;
        $this->provider = $provider;
    }

    /**
     * Returns an associative array with data treated by the provider.
     *
     * @param string $address An address (IP or street).
     *
     * @throws NoResultException           If the address could not be resolved
     * @throws InvalidCredentialsException If the credentials are invalid
     * @throws UnsupportedException        If IPv4, IPv6 or street is not supported
     *
     * @return array
     */
    public function getGeocodedData($address)
    {
        $cacheId = $this->getCacheId($address);

        if ($this->cache->contains($cacheId)) {
            return $this->cache->fetch($cacheId);
        }

        $result = $this->provider->getGeocodedData($address);

        $this->cache->save($cacheId, $result);

        return $result;
    }

    /**
     * Returns an associative array with data treated by the provider.
     *
     * @param array $coordinates Coordinates (latitude, longitude).
     *
     * @throws NoResultException           If the coordinates could not be resolved
     * @throws InvalidCredentialsException If the credentials are invalid
     * @throws UnsupportedException        If reverse geocoding is not supported
     *
     * @return array
     */
    public function getReversedData(array $coordinates)
    {
        $cacheId = $this->getCacheId($coordinates);

        if ($this->cache->contains($cacheId)) {
            return $this->cache->fetch($cacheId);
        }

        $result = $this->provider->getReversedData($coordinates);

        $this->cache->save($cacheId, $result);

        return $result;
    }

    /**
     * Get the complete cache id for the given input.
     *
     * This id may be used to interact with the cache directly, e.g. deleting an entry.
     *
     * @param string|array $input An address or reverse data.
     *
     * @return string
     *
     * @see CacheProvider::getGeocodedData
     * @see CacheProvider::getReversedData
     */
    public function getCacheId($input)
    {
        if (is_string($input)) {
            return $this->cachePrefix . md5($input);
        } else {
            return $this->cacheReversePrefix . md5(implode('_', $input));
        }
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

    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function getCache()
    {
        return $this->cache;
    }
}
