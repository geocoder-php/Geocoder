<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IP2Location\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\IP2Location\IP2Location;
use Http\Client\HttpClient;

/**
 * @author IP2Location <support@ip2location.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected $testAddress = false;

    protected $testReverse = false;

    protected function createProvider(HttpClient $httpClient)
    {
        return new IP2Location($httpClient, $this->getApiKey());
    }

    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey()
    {
        if (!isset($_SERVER['IP2Location_API_KEY'])) {
            $this->markTestSkipped('No IP2Location API key');
        }

        return $_SERVER['IP2Location_API_KEY'];
    }
}
