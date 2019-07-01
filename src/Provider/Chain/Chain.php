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
use Geocoder\Exception\LogicException;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\Provider;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
final class Chain implements Provider, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Provider[]
     */
    private $providers = [];

    /**
     * @var \Throwable[]|null
     */
    private $previousQueryExceptions;

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
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $this->previousQueryExceptions = [];

        foreach ($this->providers as $provider) {
            try {
                $result = $provider->geocodeQuery($query);

                if (!$result->isEmpty()) {
                    return $result;
                }
            } catch (\Throwable $e) {
                $this->previousQueryExceptions[] = $e;

                $this->log(
                    'alert',
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

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $this->previousQueryExceptions = [];

        foreach ($this->providers as $provider) {
            try {
                $result = $provider->reverseQuery($query);

                if (!$result->isEmpty()) {
                    return $result;
                }
            } catch (\Throwable $e) {
                $this->previousQueryExceptions[] = $e;

                $coordinates = $query->getCoordinates();
                $this->log(
                    'alert',
                    sprintf('Provider "%s" could reverse coordinates: %f, %f.', $provider->getName(),
                        $coordinates->getLatitude(), $coordinates->getLongitude()),
                    ['exception' => $e]
                );
            }
        }

        return new AddressCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
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
    public function add(Provider $provider): self
    {
        $this->providers[] = $provider;

        return $this;
    }

    /**
     * @return \Throwable[]
     */
    public function getPreviousQueryExceptions(): array
    {
        if (null === $this->previousQueryExceptions) {
            throw new LogicException('A query must be made before retrieving exceptions');
        }

        return $this->previousQueryExceptions;
    }

    /**
     * @param       $level
     * @param       $message
     * @param array $context
     */
    private function log($level, $message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}
