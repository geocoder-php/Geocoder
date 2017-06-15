<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\TomTom\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\BingMaps\BingMaps;
use Geocoder\Provider\TomTom\TomTom;
use Http\Client\HttpClient;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected $skippedTests = [];

    protected $testIpv4 = false;
    protected $testIpv6 = false;

    protected function createProvider(HttpClient $httpClient)
    {
        return new TomTom($httpClient, $this->getApiKey());
    }

    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey()
    {
        return $_SERVER['TOMTOM_MAP_KEY'];
    }
}
