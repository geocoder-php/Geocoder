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
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

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
     * A callable that decided what provider to use.
     *
     * @var callable
     */
    private $decider;

    public function __construct(?callable $decider = null, int $limit = Geocoder::DEFAULT_RESULT_LIMIT)
    {
        $this->limit = $limit;
        $this->decider = $decider ?? __CLASS__.'::getProvider';
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        return call_user_func($this->decider, $query, $this->providers, $this->provider)->geocodeQuery($query);
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        return call_user_func($this->decider, $query, $this->providers, $this->provider)->reverseQuery($query);
    }

    public function getName(): string
    {
        return 'provider_aggregator';
    }

    public function geocode(string $value): Collection
    {
        return $this->geocodeQuery(GeocodeQuery::create($value)
            ->withLimit($this->limit));
    }

    public function reverse(float $latitude, float $longitude): Collection
    {
        return $this->reverseQuery(ReverseQuery::create(new Coordinates($latitude, $longitude))
            ->withLimit($this->limit));
    }

    /**
     * Registers a new provider to the aggregator.
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
     */
    public function using(string $name): self
    {
        if (!isset($this->providers[$name])) {
            throw ProviderNotRegistered::create($name, array_keys($this->providers));
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
     * Get a provider to use for this query.
     *
     * @param GeocodeQuery|ReverseQuery $query
     * @param Provider[]                $providers
     *
     * @throws ProviderNotRegistered
     */
    private static function getProvider($query, array $providers, ?Provider $currentProvider = null): Provider
    {
        if (null !== $currentProvider) {
            return $currentProvider;
        }

        if ([] === $providers) {
            throw ProviderNotRegistered::noProviderRegistered();
        }

        // Take first
        $key = key($providers);

        return $providers[$key];
    }
}
