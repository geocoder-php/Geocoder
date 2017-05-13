<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Chain;

use Geocoder\Exception\ChainZeroResults;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Model\Query\GeocodeQuery;
use Geocoder\Model\Query\ReverseQuery;
use Geocoder\Provider\LocaleAwareGeocoder;
use Geocoder\Provider\Provider;

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
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query)
    {
        $exceptions = [];
        foreach ($this->providers as $provider) {
            try {
                return $provider->geocodeQuery($query);
            } catch (InvalidCredentials $e) {
                throw $e;
            } catch (\Exception $e) {
                $exceptions[] = $e;
            }
        }

        throw new ChainZeroResults(sprintf('No provider could geocode address: "%s".', $query->getText()), $exceptions);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query)
    {
        $exceptions = [];
        foreach ($this->providers as $provider) {
            try {
                return $provider->reverseQuery($query);
            } catch (InvalidCredentials $e) {
                throw $e;
            } catch (\Exception $e) {
                $exceptions[] = $e;
            }
        }

        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();
        throw new ChainZeroResults(sprintf('No provider could reverse coordinates: %f, %f.', $latitude, $longitude), $exceptions);
    }

    /**
     * {@inheritdoc}
     */
    public function limit($limit)
    {
        foreach ($this->providers as $provider) {
            $provider->limit($limit);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit()
    {
        throw new \LogicException('The `Chain` provider is not able to return the limit value.');
    }

    /**
     * {@inheritdoc}
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
