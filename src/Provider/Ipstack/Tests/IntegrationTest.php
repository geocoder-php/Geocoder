<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Ipstack\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\Ipstack\Ipstack;
use Psr\Http\Client\ClientInterface;

/**
 * @author Jonas Gielen <gielenjonas@gmail.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected bool $testAddress = false;

    protected bool $testReverse = false;

    protected function createProvider(ClientInterface $httpClient)
    {
        return new Ipstack($httpClient, $this->getApiKey());
    }

    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey(): string
    {
        return $_SERVER['IPSTACK_API_KEY'];
    }
}
