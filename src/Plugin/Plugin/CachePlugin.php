<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Plugin\Plugin;

use Geocoder\Model\Coordinates;
use Geocoder\Plugin\Plugin;
use Geocoder\Query\Query;
use Geocoder\Query\ReverseQuery;
use Psr\SimpleCache\CacheInterface;

/**
 * Cache the result of a query.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class CachePlugin implements Plugin
{
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
     * @var int|null
     */
    private $precision;

    /**
     * @param CacheInterface $cache
     * @param int|null       $lifetime
     * @param int|null       $precision
     */
    public function __construct(CacheInterface $cache, ?int $lifetime = null, ?int $precision = null)
    {
        $this->cache = $cache;
        $this->lifetime = $lifetime;
        $this->precision = $precision;
    }

    /**
     * {@inheritdoc}
     */
    public function handleQuery(Query $query, callable $next, callable $first)
    {
        $cacheKey = $this->getCacheKey($query);
        if (null !== $cachedResult = $this->cache->get($cacheKey)) {
            return $cachedResult;
        }

        $result = $next($query);
        $this->cache->set($cacheKey, $result, $this->lifetime);

        return $result;
    }

    /**
     * @param Query $query
     *
     * @return string
     */
    private function getCacheKey(Query $query): string
    {
        if (null !== $this->precision && $query instanceof ReverseQuery) {
            $query = $query->withCoordinates(new Coordinates(
                number_format($query->getCoordinates()->getLatitude(), $this->precision),
                number_format($query->getCoordinates()->getLongitude(), $this->precision)
            ));
        }

        // Include the major version number of the geocoder to avoid issues unserializing.
        return 'v4'.sha1((string) $query);
    }
}
