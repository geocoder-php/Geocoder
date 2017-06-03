<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GeoIP2\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\BingMaps\BingMaps;
use Geocoder\Provider\GeoIP2\GeoIP2;
use Geocoder\Provider\GeoIP2\GeoIP2Adapter;
use GeoIp2\Database\Reader;
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
        $reader = new Reader(__DIR__.'/fixtures/GeoLite2-City.mmdb');

        return new GeoIP2(new GeoIP2Adapter($reader));
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
