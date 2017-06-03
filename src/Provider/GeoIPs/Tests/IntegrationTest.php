<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GeoIPs\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\GeoIPs\GeoIPs;
use Http\Client\HttpClient;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected $skippedTests = [
        'testGeocodeQuery' => 'The provider does not support street addresses.',
        'testGeocodeQueryWithNoResults' => 'The provider does not support street addresses.',
        'testReverseQuery' => 'The provider does not support reverse.',
        'testEmptyReverseQuery' => 'The provider does not support reverse.',
        'testServer500Error' => 'The provider does not support street addresses.',
        'testServer500ErrorReverse' => 'The provider does not support street addresses.',
        'testServer400Error' => 'The provider does not support street addresses.',
        'testServer400ErrorReverse' => 'The provider does not support street addresses.',
        'testServerEmptyResponse' => 'The provider does not support street addresses.',
        'testServerEmptyResponseReverse' => 'The provider does not support street addresses.',
        'testQuotaExceededResponse' => 'The provider does not support street addresses.',
        'testQuotaExceededResponseReverse' => 'The provider does not support street addresses.',
        'testInvalidCredentialsResponse' => 'The provider does not support street addresses.',
        'testInvalidCredentialsResponseReverse' => 'The provider does not support street addresses.',
    ];

    protected function createProvider(HttpClient $httpClient)
    {
        return new GeoIPs($httpClient, $this->getApiKey());
    }

    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey()
    {
        return $_SERVER['GEOIPS_API_KEY'];
    }
}
