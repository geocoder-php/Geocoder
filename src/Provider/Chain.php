<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\ChainZeroResults;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\Query\GeocodeQuery;
use Geocoder\Model\Query\ReverseQuery;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
final class Chain implements LocaleAwareGeocoder, Provider
{

    /**
     * @var Provider[]
     */
    private $providers = [];

    /**
     * @param Provider[] $providers
     */
    public function __construct(array $providers = [])
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritDoc}
     */
    public function geocodeQuery(GeocodeQuery $query)
    {
        $address = $query->getText();
        $locale = $query->getLocale();
        $exceptions = [];
        foreach ($this->providers as $provider) {
            if ($provider instanceof LocaleAwareGeocoder && $locale !== null) {
                $provider = clone $provider;
                $provider->setLocale($locale);
            }
            try {
                return $provider->geocode($address);
            } catch (InvalidCredentials $e) {
                throw $e;
            } catch (\Exception $e) {
                $exceptions[] = $e;
            }
        }

        throw new ChainZeroResults(sprintf('No provider could geocode address: "%s".', $address), $exceptions);
    }

    /**
     * {@inheritDoc}
     */
    public function reverseQuery(ReverseQuery $query)
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();
        $exceptions = [];
        foreach ($this->providers as $provider) {
            try {
                return $provider->reverse($latitude, $longitude);
            } catch (InvalidCredentials $e) {
                throw $e;
            } catch (\Exception $e) {
                $exceptions[] = $e;
            }
        }

        throw new ChainZeroResults(sprintf('No provider could reverse coordinates: %f, %f.', $latitude, $longitude), $exceptions);
    }

    /**
     * {@inheritDoc}
     */
    public function limit($limit)
    {
        foreach ($this->providers as $provider) {
            $provider->limit($limit);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getLimit()
    {
        throw new \LogicException("The `Chain` provider is not able to return the limit value.");
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'chain';
    }

    /**
     * Adds a provider.
     *
     * @param Provider $provider
     *
     * @return Chain
     */
    public function add(Provider $provider)
    {
        $this->providers[] = $provider;

        return $this;
    }
}
