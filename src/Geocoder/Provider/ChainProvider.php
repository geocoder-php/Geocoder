<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class ChainProvider implements ProviderInterface
{
    /**
     * @var array
     */
    private $providers = array();

    /**
     * Constructor
     *
     * @param array $providers
     */
    public function __construct(array $providers = array())
    {
        $this->providers = $providers;
    }

    /**
     * Add a provider
     *
     * @param ProviderInterface $provider
     */
    public function addProvider(ProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        foreach ($this->providers as $provider) {
            try {
                return $provider->getGeocodedData($address);
            } catch (\Exception $e) {
            }
        }

        throw new \RuntimeException(sprintf('No provider could provide the address "%"', $address));
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        foreach ($this->providers as $provider) {
            try {
                return $provider->getReversedData($coordinates);
            } catch (\Exception $e) {
            }
        }

        throw new \RuntimeException(sprintf('No provider could provide the coordinated %s', json_encode($coordinates)));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'chain';
    }
}
