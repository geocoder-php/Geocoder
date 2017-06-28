<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\PickPoint\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\PickPoint\PickPoint;
use Http\Client\HttpClient;

/**
 * @author Vladimir Kalinkin <vova.kalinkin@gmail.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected function createProvider(HttpClient $httpClient)
    {
        return new PickPoint($httpClient, $this->getApiKey());
    }

    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey()
    {
        return $_SERVER['PICKPOINT_API_KEY'];
    }
}
