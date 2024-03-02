<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Psr\Http\Client\ClientInterface;

class IntegrationTest extends ProviderIntegrationTest
{
    protected array $skippedTests = [
        'testReverseQueryWithNoResults' => 'AzureMaps API returns "position":"0.000000,0.000000" for reverse query at 0,0.',
    ];

    /**
     * @return Geocoder\Provider\Provider that is used in the tests
     */
    protected function createProvider(ClientInterface $httpClient)
    {
        return new Geocoder\Provider\AzureMaps\AzureMaps($httpClient, $_SERVER['AZURE_MAPS_SUBSCRIPTION_KEY']);
    }

    /**
     * @return string the directory where cached responses are stored
     */
    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    /**
     * @return string the API key or substring to be removed from cache
     */
    protected function getApiKey(): string
    {
        return $_SERVER['AZURE_MAPS_SUBSCRIPTION_KEY'];
    }
}
