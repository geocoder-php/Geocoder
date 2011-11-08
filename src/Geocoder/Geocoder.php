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

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class Geocoder implements GeocoderInterface
{
    /**
     * @var double
     */
    protected $latitude;

    /**
     * @var double
     */
    protected $longitude;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var string
     */
    protected $zipcode;

    /**
     * @var string
     */
    protected $region;

    /**
     * @var string
     */
    protected $country;

    /**
     * @var array
     */
    private $providers = array();

    /**
     * @var \Geocoder\Provider\ProviderInterface
     */
    private $provider;

    /**
     * @param \Geocoder\Provider\ProviderInterface $provider
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
        $data = $this->getProvider()->getGeocodedData(trim($value));
        $this->extractData($data);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        $data = $this->getProvider()->getReversedData(array($latitude, $longitude));
        $this->extractData($data);
    }

    /**
     * {@inheritDoc}
     */
    public function getCoordinates()
    {
        return array($this->getLatitude(), $this->getLongitude());
    }

    /**
     * {@inheritDoc}
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * {@inheritDoc}
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * {@inheritDoc}
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * {@inheritDoc}
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * {@inheritDoc}
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * {@inheritDoc}
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Registers a provider.
     *
     * @param \Geocoder\Provider\ProviderInterface $provider
     * @return \Geocoder\AbstractGeocoder
     */
    public function registerProvider(ProviderInterface $provider)
    {
        if (null !== $provider) {
            $this->providers[$provider->getName()] = $provider;
        }

        return $this;
    }

    /**
     * Registers a set of providers..
     *
     * @param array $providers
     * @return \Geocoder\AbstractGeocoder
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
     * @param string $name  A provider's name
     * @return \Geocoder\AbstractGeocoder
     */
    public function using($name)
    {
        if (isset($this->providers[$name])) {
            $this->provider = $this->providers[$name];
        }

        return $this;
    }

    /**
     * Returns the provider to use.
     *
     * @return \Geocoder\Provider\ProviderInterface
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
     * Extracts data from an array.
     *
     * @param array $data   An array.
     */
    protected function extractData(array $data = array())
    {
        if (isset($data['latitude'])) {
            $this->latitude = $data['latitude'];
        }
        if (isset($data['longitude'])) {
            $this->longitude = $data['longitude'];
        }
        if (isset($data['city'])) {
            $this->city = $data['city'];
        }
        if (isset($data['zipcode'])) {
            $this->zipcode = $data['zipcode'];
        }
        if (isset($data['region'])) {
            $this->region = $data['region'];
        }
        if (isset($data['country'])) {
            $this->country = $data['country'];
        }
    }
}
