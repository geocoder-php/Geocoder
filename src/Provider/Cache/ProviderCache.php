<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Cache;

use Geocoder\Collection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\Provider;
use Psr\SimpleCache\CacheInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class ProviderCache implements Provider
{
    /**
     * @var Provider
     */
    private $realProvider;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * How log a result is going to be cached.
     *
     * @var int|null
     */
    private $lifetime;

    /**
     * @param Provider       $realProvider
     * @param CacheInterface $cache
     * @param int            $lifetime
     */
    public function __construct(Provider $realProvider, CacheInterface $cache, int $lifetime = null)
    {
        $this->realProvider = $realProvider;
        $this->cache = $cache;
        $this->lifetime = $lifetime;
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $cacheKey = $this->getCacheKey($query);
        if (null !== $result = $this->cache->get($cacheKey)) {
            return $result;
        }

        $result = $this->realProvider->geocodeQuery($query);
        $this->cache->set($cacheKey, $result, $this->lifetime);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $cacheKey = $this->getCacheKey($query);
        if (null !== $result = $this->cache->get($cacheKey)) {
            return $result;
        }

        $result = $this->realProvider->reverseQuery($query);
        $this->cache->set($cacheKey, $result, $this->lifetime);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return sprintf('%s (cache)', $this->realProvider->getName());
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->realProvider, $method], $args);
    }

    /**
     * @param GeocodeQuery|ReverseQuery $query
     *
     * @return string
     */
    private function getCacheKey($query): string
    {
        // Include the major version number of the geocoder to avoid issues unserializing.
        return 'v4'.sha1((string) $query);
    }
}
