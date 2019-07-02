<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Plugin;

use Geocoder\Collection;
use Geocoder\Exception\Exception;
use Geocoder\Exception\LogicException;
use Geocoder\Plugin\Promise\GeocoderFulfilledPromise;
use Geocoder\Plugin\Promise\GeocoderRejectedPromise;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\LookupQuery;
use Geocoder\Query\Query;
use Geocoder\Query\ReverseQuery;
use Geocoder\Plugin\Exception\LoopException;

/**
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class PluginProvider implements Provider
{
    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var Plugin[]
     */
    private $plugins;

    /**
     * A list of options.
     *
     * @var array
     */
    private $options;

    /**
     * @param Provider $provider
     * @param Plugin[] $plugins
     * @param array    $options  {
     *
     *     @var int      $max_restarts
     * }
     */
    public function __construct(Provider $provider, array $plugins = [], array $options = [])
    {
        $this->provider = $provider;
        $this->plugins = $plugins;
        $this->options = $this->configure($options);
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $pluginChain = $this->createPluginChain($this->plugins, function (GeocodeQuery $query) {
            try {
                return new GeocoderFulfilledPromise($this->provider->geocodeQuery($query));
            } catch (Exception $exception) {
                return new GeocoderRejectedPromise($exception);
            }
        });

        return $pluginChain($query)->wait();
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $pluginChain = $this->createPluginChain($this->plugins, function (ReverseQuery $query) {
            try {
                return new GeocoderFulfilledPromise($this->provider->reverseQuery($query));
            } catch (Exception $exception) {
                return new GeocoderRejectedPromise($exception);
            }
        });

        return $pluginChain($query)->wait();
    }

    /**
     * {@inheritdoc}
     */
    public function lookupQuery(LookupQuery $query): Collection
    {
        $pluginChain = $this->createPluginChain($this->plugins, function (LookupQuery $query) {
            try {
                return new GeocoderFulfilledPromise($this->provider->lookupQuery($query));
            } catch (Exception $exception) {
                return new GeocoderRejectedPromise($exception);
            }
        });

        return $pluginChain($query)->wait();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->provider->getName();
    }

    /**
     * Configure the plugin provider.
     *
     * @param array $options
     *
     * @return array
     */
    private function configure(array $options = []): array
    {
        $defaults = [
            'max_restarts' => 10,
        ];

        $config = array_merge($defaults, $options);

        // Make sure no invalid values are provided
        if (count($config) !== count($defaults)) {
            throw new LogicException(sprintf('Valid options to the PluginProviders are: %s', implode(', ', array_values($defaults))));
        }

        return $config;
    }

    /**
     * Create the plugin chain.
     *
     * @param Plugin[] $pluginList     A list of plugins
     * @param callable $clientCallable Callable making the HTTP call
     *
     * @return callable
     */
    private function createPluginChain(array $pluginList, callable $clientCallable)
    {
        $firstCallable = $lastCallable = $clientCallable;

        while ($plugin = array_pop($pluginList)) {
            $lastCallable = function (Query $query) use ($plugin, $lastCallable, &$firstCallable) {
                return $plugin->handleQuery($query, $lastCallable, $firstCallable);
            };

            $firstCallable = $lastCallable;
        }

        $firstCalls = 0;
        $firstCallable = function (Query $query) use ($lastCallable, &$firstCalls) {
            if ($firstCalls > $this->options['max_restarts']) {
                throw LoopException::create('Too many restarts in plugin provider', $query);
            }

            ++$firstCalls;

            return $lastCallable($query);
        };

        return $firstCallable;
    }
}
