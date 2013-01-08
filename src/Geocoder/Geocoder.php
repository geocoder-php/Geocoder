<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder;

use Geocoder\Provider\ProviderInterface;
use Geocoder\Result\Geocoded;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class Geocoder implements GeocoderInterface
{
    /**
     * Version
     */
    const VERSION = '1.1.6';

    /**
     * @var ProviderInterface[]
     */
    private $providers = array();

    /**
     * @var ProviderInterface
     */
    private $provider = null;

    /**
     * @param ProviderInterface $provider
     */
    public function __construct(ProviderInterface $provider = null)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($value)
    {
        if (empty($value)) {
            // let's save a request
            return $this->returnResult(array());
        }

        $data   = $this->getProvider()->getGeocodedData(trim($value));
        $result = $this->returnResult($data);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        if (empty($latitude) || empty($longitude)) {
            // let's save a request
            return $this->returnResult(array());
        }

        $data   = $this->getProvider()->getReversedData(array($latitude, $longitude));
        $result = $this->returnResult($data);

        return $result;
    }

    /**
     * Registers a provider.
     *
     * @param ProviderInterface $provider
     *
     * @return GeocoderInterface
     */
    public function registerProvider(ProviderInterface $provider)
    {
        if (null !== $provider) {
            $this->providers[$provider->getName()] = $provider;
        }

        return $this;
    }

    /**
     * Registers a set of providers.
     *
     * @param ProviderInterface[] $providers
     *
     * @return GeocoderInterface
     */
    public function registerProviders(array $providers = array())
    {
        foreach ($providers as $provider) {
            $this->registerProvider($provider);
        }

        return $this;
    }

    /**
     * Sets the provider to use.
     *
     * @param string $name A provider's name
     *
     * @return GeocoderInterface
     */
    public function using($name)
    {
        if (isset($this->providers[$name])) {
            $this->provider = $this->providers[$name];
        }

        return $this;
    }

    /**
     * Returns registered providers indexed by name.
     *
     * @return ProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Returns the provider to use.
     *
     * @return ProviderInterface
     */
    protected function getProvider()
    {
        if (null === $this->provider) {
            if (0 === count($this->providers)) {
                throw new \RuntimeException('No provider registered.');
            } else {
                $this->provider = $this->providers[key($this->providers)];
            }
        }

        return $this->provider;
    }

    /**
     * @param array $data An array of data.
     *
     * @return Geocoded
     */
    protected function returnResult(array $data = array())
    {
        $result = new Geocoded();
        $result->fromArray($data);

        return $result;
    }
}
