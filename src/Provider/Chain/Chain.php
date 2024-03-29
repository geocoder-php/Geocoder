<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Chain;

use Geocoder\Collection;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
final class Chain implements Provider, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    private $geocodeQueryLogLevel = LogLevel::ALERT;

    /**
     * @var string
     */
    private $reverseQueryLogLevel = LogLevel::ALERT;

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

    public function setGeocodeQueryLogLevel(string $level): void
    {
        $this->geocodeQueryLogLevel = $level;
    }

    public function setReverseQueryLogLevel(string $level): void
    {
        $this->reverseQueryLogLevel = $level;
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        foreach ($this->providers as $provider) {
            try {
                $result = $provider->geocodeQuery($query);

                if (!$result->isEmpty()) {
                    return $result;
                }
            } catch (\Throwable $e) {
                $this->log(
                    $this->geocodeQueryLogLevel,
                    'Provider "{providerName}" could not geocode address: "{address}".',
                    [
                        'exception' => $e,
                        'providerName' => $provider->getName(),
                        'address' => $query->getText(),
                    ]
                );
            }
        }

        return new AddressCollection();
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        foreach ($this->providers as $provider) {
            try {
                $result = $provider->reverseQuery($query);

                if (!$result->isEmpty()) {
                    return $result;
                }
            } catch (\Throwable $e) {
                $coordinates = $query->getCoordinates();

                $this->log(
                    $this->reverseQueryLogLevel,
                    'Provider "{providerName}" could not reverse geocode coordinates: {latitude}, {longitude}".',
                    [
                        'exception' => $e,
                        'providerName' => $provider->getName(),
                        'latitude' => $coordinates->getLatitude(),
                        'longitude' => $coordinates->getLongitude(),
                    ]
                );
            }
        }

        return new AddressCollection();
    }

    public function getName(): string
    {
        return 'chain';
    }

    /**
     * Adds a provider.
     */
    public function add(Provider $provider): self
    {
        $this->providers[] = $provider;

        return $this;
    }

    /**
     * @param mixed[] $context
     */
    private function log(mixed $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}
