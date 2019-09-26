<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IpFinder\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\IpFinder\IpFinder;
use Http\Client\HttpClient;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected $testAddress = false;

    protected $testReverse = false;

    protected function createProvider(HttpClient $httpClient)
    {
        return new IpFinder($httpClient, $this->getApiKey());
    }

    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey()
    {
        return $_SERVER['IPFINDER_API_KEY'];
    }
}
