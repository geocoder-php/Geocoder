<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Here\Tests;

require_once('OverrideCachedResponseClient.php');

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\Here\Here;
use Http\Client\HttpClient;

/**
 * @author Sébastien Barré <sebastien@sheub.eu>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected $testIpv4 = false;

    protected $testIpv6 = false;
    
    protected function createProvider(HttpClient $httpClient)
    {
        return new Here($httpClient, $this->getAppId(), $this->getAppCode());
    }

    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    /**
     * This client will make real request if cache was not found.
     *
     * @return CachedResponseClient
     */
    private function getCachedHttpClient()
    {
        try {
            $client = HttpClientDiscovery::find();
        } catch (\Http\Discovery\NotFoundException $e) {
            $client = $this->getMockForAbstractClass(HttpClient::class);

            $client
                ->expects($this->any())
                ->method('sendRequest')
                ->willThrowException($e);
        }

        return new CachedResponseClient($client, $this->getCacheDir(), $this->getAppId(), $this->getAppCode());
    }

    protected function getApiKey()
    {
        return $_SERVER['HERE_APP_ID'];
    }

    protected function getAppId()
    {
        return $_SERVER['HERE_APP_ID'];
    }

    /**
    * @return string the Here AppCode or substring to be removed from cache.
    */
    protected function getAppCode()
    {
        return $_SERVER['HERE_APP_CODE'];
    }
}
