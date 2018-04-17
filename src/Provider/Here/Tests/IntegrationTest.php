<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Here\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\Here\Here;
use Http\Client\HttpClient;

/**
 * @author Sébastien Barré <sebastien@sheub.eu>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected $testAddress = true;

    protected $testReverse = true;

    protected $testIpv4 = true;

    protected $testIpv6 = false;

    protected function createProvider(HttpClient $httpClient)
    {
        return new Here($httpClient, self::getAppId(), self::getAppCode());
    }

    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey()
    {
        return $_SERVER['HERE_APP_ID'];
    }

    protected function getAppId()
    {
        return $_SERVER['HERE_APP_ID'];
    }

    protected function getAppCode()
    {
        return $_SERVER['HERE_APP_CODE'];
    }
}
