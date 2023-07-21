<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Exception;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class ProviderNotRegistered extends \RuntimeException implements Exception
{
    /**
     * @param string[] $registeredProviders
     */
    public static function create(string $providerName, array $registeredProviders = []): self
    {
        return new self(sprintf(
            'Provider "%s" is not registered, so you cannot use it. Did you forget to register it or made a typo?%s',
            $providerName,
            0 == count($registeredProviders) ? '' : sprintf(' Registered providers are: %s.', implode(', ', $registeredProviders))
        ));
    }

    public static function noProviderRegistered(): self
    {
        return new self('No provider registered.');
    }
}
