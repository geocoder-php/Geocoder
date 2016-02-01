<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\CacheStrategy\Strategy;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class Cache implements LocaleAwareProvider
{
    use LocaleTrait;

    /**
     * @var Strategy
     */
    private $strategy;

    /**
     * @var Provider
     */
    private $delegate;

    /**
     * @param CacheItemPoolInterface $cache
     * @param Provider               $delegate
     */
    public function __construct(Strategy $strategy, Provider $delegate)
    {
        $this->delegate = $delegate;
        $this->strategy = $strategy;
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        $key = $this->generateKey($address);

        return $this->strategy->invoke($key, function() use ($address) {
            return $this->delegate->geocode($address);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        $key = $this->generateKey(serialize([$latitude, $longitude]));

        return $this->strategy->invoke($key, function() use ($latitude, $longitude) {
            return $this->delegate->reverse($latitude, $longitude);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function limit($limit)
    {
        $this->delegate->limit($limit);
    }

    /**
     * {@inheritDoc}
     */
    public function getLimit()
    {
        return $this->delegate->getLimit();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'cache';
    }

    /**
     * Generate a key.
     *
     * @param string $value
     *
     * @return string
     */
    private function generateKey($value)
    {
        return 'geocoder_'.sha1($value);
    }
}
