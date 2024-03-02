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
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\SimpleCache\CacheInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ProviderCache implements Provider
{
    /**
     * @var Provider
     */
    protected $realProvider;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * How long a result is going to be cached.
     *
     * @var int|null
     */
    protected $lifetime;

    /**
     * If true, include the real provider name into the cache key.
     */
    private bool $separateCache;

    final public function __construct(Provider $realProvider, CacheInterface $cache, ?int $lifetime = null, bool $separateCache = false)
    {
        $this->realProvider = $realProvider;
        $this->cache = $cache;
        $this->lifetime = $lifetime;
        $this->separateCache = $separateCache;
    }

    final public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $cacheKey = $this->getCacheKey($query);
        if (null !== $result = $this->cache->get($cacheKey)) {
            return $result;
        }

        $result = $this->realProvider->geocodeQuery($query);
        $this->cache->set($cacheKey, $result, $this->lifetime);

        return $result;
    }

    final public function reverseQuery(ReverseQuery $query): Collection
    {
        $cacheKey = $this->getCacheKey($query);
        if (null !== $result = $this->cache->get($cacheKey)) {
            return $result;
        }

        $result = $this->realProvider->reverseQuery($query);
        $this->cache->set($cacheKey, $result, $this->lifetime);

        return $result;
    }

    public function getName(): string
    {
        return sprintf('%s (cache)', $this->realProvider->getName());
    }

    final public function __call(string $method, array $args): mixed
    {
        return call_user_func_array([$this->realProvider, $method], $args);
    }

    /**
     * @param GeocodeQuery|ReverseQuery $query
     */
    protected function getCacheKey($query): string
    {
        // Include the major version number of the geocoder to avoid issues unserializing
        // and real provider name if we want to separate cache
        return 'v4'.sha1((string) $query.($this->separateCache ? $this->realProvider->getName() : ''));
    }
}
