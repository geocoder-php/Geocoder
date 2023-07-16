<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\MapTiler\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\MapTiler\MapTiler;
use Psr\Http\Client\ClientInterface;

/**
 * @author Jonathan Beliën
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected bool $testAddress = true;

    protected bool $testReverse = true;

    protected bool $testIpv4 = false;

    protected bool $testIpv6 = false;

    protected array $skippedTests = [];

    protected function createProvider(ClientInterface $httpClient)
    {
        return new MapTiler($httpClient, $this->getApiKey());
    }

    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey(): string
    {
        return $_SERVER['MAPTILER_KEY'];
    }
}
