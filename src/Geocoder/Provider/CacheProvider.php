<?php

namespace Geocoder\Provider;

use Geocoder\CacheAdapter\CacheInterface;

/**
 *  @author Markus Bachmann <markus.bachmann@digital-connect,de>
 */
class CacheProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var CacheInterface
     */
    protected $cacheAdapter;

    /**
     * @var ProviderInterface
     */
    protected $provider;

    public function __construct(CacheInterface $cache, ProviderInterface $provider)
    {
        $this->cacheAdapter = $cache;
        $this->provider = $provider;
    }

    /**
     * Get the provider
     *
     * @return ProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set a provider
     *
     * @param ProviderInterface $provider
     */
    public function setProvider(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Get the CacheAdapter
     *
     * @return CacheInterface
     */
    public function getCacheAdapter()
    {
        return $this->cacheAdapter;
    }

    /**
     * Set the cache adapter
     *
     * @param CacheInterface $adapter
     */
    public function setCacheAdapter(CacheInterface $adapter)
    {
        $this->cacheAdapter = $adapter;
    }

    /**
     * Returns an associative array with data treated by the provider.
     *
     * @param string $address   An address (IP or street).
     * @return array
     */
    public function getGeocodedData($address)
    {
        $key = sha1($address);

        $result = $this->cacheAdapter->retrieve($key);

        if (null === $result) {
            $result = $this->provider->getGeocodedData($address);
            $this->cacheAdapter->store($key, $result);
        }

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

        $result = $this->cacheAdapter->retrieve($key);

        if (null === $result) {
            $result = $this->provider->getReversedData($coordinates);
            $this->cacheAdapter->store($key, $result);
        }

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
