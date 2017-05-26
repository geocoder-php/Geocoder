<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\FreeGeoIp\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\FreeGeoIp\FreeGeoIp;
use Http\Client\HttpClient;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected $skippedTests = [
        'testGeocodeQuery' => 'The FreeGeoIp provider does not support street addresses.',
        'testGeocodeQueryWithNoResults' => 'The FreeGeoIp provider does not support street addresses.',
        'testReverseQuery' => 'The FreeGeoIp provider does not support street addresses.',
        'testEmptyReverseQuery' => 'The FreeGeoIp provider does not support street addresses.',
        'testServer500Error' => 'The FreeGeoIp provider does not support street addresses.',
        'testServer500ErrorReverse' => 'The FreeGeoIp provider does not support street addresses.',
        'testServer400Error' => 'The FreeGeoIp provider does not support street addresses.',
        'testServer400ErrorReverse' => 'The FreeGeoIp provider does not support street addresses.',
        'testServerEmptyResponse' => 'The FreeGeoIp provider does not support street addresses.',
        'testServerEmptyResponseReverse' => 'The FreeGeoIp provider does not support street addresses.',
        'testQuotaExceededResponse' => 'The FreeGeoIp provider does not support street addresses.',
        'testQuotaExceededResponseReverse' => 'The FreeGeoIp provider does not support street addresses.',
        'testInvalidCredentialsResponse' => 'The FreeGeoIp provider does not support street addresses.',
        'testInvalidCredentialsResponseReverse' => 'The FreeGeoIp provider does not support street addresses.',
    ];

    protected function createProvider(HttpClient $httpClient)
    {
        return new FreeGeoIp($httpClient);
    }

    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey()
    {
        return null;
    }
}
