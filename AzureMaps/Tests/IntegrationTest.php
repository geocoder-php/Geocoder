<?php

use Geocoder\IntegrationTest\ProviderIntegrationTest;

/**
 * Created by PhpStorm.
 * User: Max Langerman
 * Date: 7/15/20
 * Time: 10:36 PM
 */

class IntegrationTest extends ProviderIntegrationTest
{

    /**
     * @return \Geocoder\Provider\Provider that is used in the tests.
     */
    protected function createProvider(\Http\Client\HttpClient $httpClient)
    {
        return new \Geocoder\Provider\AzureMaps\AzureMaps($httpClient, $_SERVER['AZURE_MAPS_SUBSCRIPTION_KEY']);
    }

    /**
     * @return string the directory where cached responses are stored
     */
    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    /**
     * @return string the API key or substring to be removed from cache.
     */
    protected function getApiKey()
    {
        return $_SERVER['AZURE_MAPS_SUBSCRIPTION_KEY'];
    }
}
