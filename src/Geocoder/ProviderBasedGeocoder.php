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
use Geocoder\Model\AddressFactory;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class ProviderBasedGeocoder implements Geocoder
{
    /**
     * @var integer
     */
    const MAX_RESULTS = 5;

    /**
     * @var Provider[]
     */
    private $providers = [];

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var AddressFactory
     */
    private $factory;

    /**
     * @var integer
     */
    private $maxResults;

    /**
     * @param Provider $provider
     * @param integer  $maxResults
     */
    public function __construct(Provider $provider = null, $maxResults = self::MAX_RESULTS)
    {
        $this->provider = $provider;
        $this->factory  = new AddressFactory();

        $this->limit($maxResults);
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($value)
    {
        if (empty($value)) {
            // let's save a request
            return [];
        }

        $provider = $this->getProvider()->setMaxResults($this->getMaxResults());
        $data     = $provider->getGeocodedData(trim($value));

        return $this->returnResult($data);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        if (empty($latitude) || empty($longitude)) {
            // let's save a request
            return [];
        }

        $provider = $this->getProvider()->setMaxResults($this->getMaxResults());
        $data     = $provider->getReversedData([ $latitude, $longitude ]);

        return $this->returnResult($data);
    }

    /**
     * Registers a provider.
     *
     * @param Provider $provider
     *
     * @return ProviderBasedGeocoder
     */
    public function registerProvider(Provider $provider)
    {
        if (null !== $provider) {
            $this->providers[$provider->getName()] = $provider;
        }

        return $this;
    }

    /**
     * Convenient method to egister a set of providers.
     *
     * @param Provider[] $providers
     *
     * @return ProviderBasedGeocoder
     */
    public function registerProviders(array $providers = [])
    {
        foreach ($providers as $provider) {
            $this->registerProvider($provider);
        }

        return $this;
    }

    /**
     * Set the provider to use.
     *
     * @param string $name A provider's name
     *
     * @return ProviderBasedGeocoder
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
     * Return registered providers indexed by name.
     *
     * @return Provider[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param integer $maxResults
     *
     * @return ProviderBasedGeocoder
     */
    public function limit($maxResults)
    {
        $this->maxResults = $maxResults;

        return $this;
    }

    /**
     * @return integer $maxResults
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * Return the provider to use.
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

    /**
     * @param array $data An array of data.
     *
     * @return Address[]
     */
    protected function returnResult(array $data = [])
    {
        return $this->factory->createFromArray($data);
    }
}
