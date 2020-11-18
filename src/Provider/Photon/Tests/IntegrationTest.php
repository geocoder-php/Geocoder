<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Photon\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\Photon\Photon;
use Http\Client\HttpClient;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected $testAddress = true;

    protected $testReverse = true;

    protected $testIpv4 = false;

    protected $testIpv6 = false;

    protected $skippedTests = [
        'testGeocodeQuery' => 'Photon API returns "Great George Street" for "10 Downing St, London, UK" query.',
        'testReverseQueryWithNoResults' => 'Photon API returns "Atlas Buoy 0.00E 0.00N" for reverse query at 0,0.',
    ];

    protected function createProvider(HttpClient $httpClient)
    {
        return Photon::withKomootServer($httpClient, 'Geocoder PHP/Photon Provider/Integration Test');
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
