<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder;

use Geocoder\Exception\ProviderNotRegistered;
use Geocoder\Provider\Provider;
use Geocoder\Model\AddressCollection;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class ProviderAggregator implements Geocoder
{
    /**
     * @var Provider[]
     */
    private $providers = [];

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var integer
     */
    private $limit;

    /**
     * @param integer $limit
     */
    public function __construct($limit = Provider::MAX_RESULTS)
    {
        $this->limit($limit);
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($value)
    {
        $value = trim($value);

        if (empty($value)) {
            // let's save a request
            return new AddressCollection();
        }

        return $this->getProvider()
            ->limit($this->getLimit())
            ->geocode($value);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        if (empty($latitude) || empty($longitude)) {
            // let's save a request
            return new AddressCollection();
        }

        return $this->getProvider()
            ->limit($this->getLimit())
            ->reverse($latitude, $longitude);
    }

    /**
     * {@inheritDoc}
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Registers a new provider to the aggregator.
     *
     * @param Provider $provider
     *
     * @return ProviderAggregator
     */
    public function registerProvider(Provider $provider)
    {
        $this->providers[$provider->getName()] = $provider;

        return $this;
    }

    /**
     * Registers a set of providers.
     *
     * @param Provider[] $providers
     *
     * @return ProviderAggregator
     */
    public function registerProviders(array $providers = [])
    {
        foreach ($providers as $provider) {
            $this->registerProvider($provider);
        }

        return $this;
    }

    /**
     * Sets the default provider to use.
     *
     * @param string $name
     *
     * @return ProviderAggregator
     */
    public function using($name)
    {
        if (!isset($this->providers[$name])) {
            throw new ProviderNotRegistered($name);
        }

        $this->provider = $this->providers[$name];

        return $this;
    }

    /**
     * Returns all registered providers indexed by their name.
     *
     * @return Provider[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Returns the current provider in use.
     *
     * @return Provider
     */
    protected function getProvider()
    {
        if (null === $this->provider) {
            if (0 === count($this->providers)) {
                throw new \RuntimeException('No provider registered.');
            }

            $this->using(key($this->providers));
        }

        return $this->provider;
    }
}
