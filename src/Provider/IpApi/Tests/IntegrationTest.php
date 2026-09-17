<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IpApi\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\IpApi\IpApi;
use Psr\Http\Client\ClientInterface;

class IntegrationTest extends ProviderIntegrationTest
{
    protected bool $testAddress = false;

    protected bool $testReverse = false;

    protected bool $testIpv6 = false;

    protected function createProvider(ClientInterface $httpClient): IpApi
    {
        return new IpApi($httpClient, $this->getApiKey());
    }

    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey(): string
    {
        if (!isset($_SERVER['IP_API_KEY'])) {
            $this->markTestSkipped('No ip-api API key');
        }

        return $_SERVER['IP_API_KEY'];
    }
}
