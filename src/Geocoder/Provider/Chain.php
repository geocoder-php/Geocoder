<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\ChainNoResult;
use Geocoder\Exception\InvalidCredentials;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
final class Chain implements LocaleAwareProvider
{
    use LocaleTrait;

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
    public function geocode($address)
    {
        $exceptions = [];
        foreach ($this->providers as $provider) {
            if ($provider instanceof LocaleAwareProvider && $this->getLocale() !== null) {
                $provider = clone $provider;
                $provider->setLocale($this->getLocale());
            }
            try {
                return $provider->geocode($address);
            } catch (InvalidCredentials $e) {
                throw $e;
            } catch (\Exception $e) {
                $exceptions[] = $e;
            }
        }

        throw new ChainNoResult(sprintf('No provider could geocode address: "%s".', $address), $exceptions);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
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

        throw new ChainNoResult(sprintf('No provider could reverse coordinates: %f, %f.', $latitude, $longitude), $exceptions);
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
