<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Ip2Geo\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\Ip2Geo\Ip2Geo;
use Psr\Http\Client\ClientInterface;

class IntegrationTest extends ProviderIntegrationTest
{
    protected bool $testAddress = false;

    protected bool $testReverse = false;

    protected bool $testIpv4 = true;

    protected bool $testIpv6 = false;

    protected function createProvider(ClientInterface $httpClient): Ip2Geo
    {
        return new Ip2Geo($httpClient, $this->getApiKey());
    }

    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey(): string
    {
        if (!isset($_SERVER['IP2GEO_API_KEY'])) {
            $this->markTestSkipped('No ip2geo API key');
        }

        return $_SERVER['IP2GEO_API_KEY'];
    }
}
