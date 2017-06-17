<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder;

use Geocoder\Exception\ProviderNotRegistered;
use Geocoder\Model\Coordinates;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\Provider;

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
     * @var int
     */
    private $limit;

    /**
     * @param int $limit
     */
    public function __construct(int $limit = Geocoder::DEFAULT_RESULT_LIMIT)
    {
        $this->limit($limit);
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        return $this->getProvider()->geocodeQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        return $this->getProvider()->reverseQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'provider_aggregator';
    }

    /**
     * {@inheritdoc}
     */
    public function geocode(string $value): Collection
    {
        return $this->geocodeQuery(GeocodeQuery::create($value)
            ->withLimit($this->limit));
    }

    /**
     * {@inheritdoc}
     */
    public function reverse($latitude, $longitude): Collection
    {
        return $this->reverseQuery(ReverseQuery::create(new Coordinates($latitude, $longitude))
            ->withLimit($this->limit));
    }

    /**
     * @param $limit
     *
     * @return $this
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
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
    public function registerProvider(Provider $provider): self
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
    public function registerProviders(array $providers = []): self
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
    public function using(string $name): self
    {
        if (!isset($this->providers[$name])) {
            throw new ProviderNotRegistered($name ?? '');
        }

        $this->provider = $this->providers[$name];

        return $this;
    }

    /**
     * Returns all registered providers indexed by their name.
     *
     * @return Provider[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Returns the current provider in use.
     *
     * @return Provider
     *
     * @throws \RuntimeException
     */
    protected function getProvider(): Provider
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
