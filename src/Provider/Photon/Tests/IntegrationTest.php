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
use Psr\Http\Client\ClientInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected bool $testAddress = true;

    protected bool $testReverse = true;

    protected bool $testIpv4 = false;

    protected bool $testIpv6 = false;

    protected array $skippedTests = [
        'testGeocodeQuery' => 'Photon API returns "Great George Street" for "10 Downing St, London, UK" query.',
        'testReverseQueryWithNoResults' => 'Photon API returns "Atlas Buoy 0.00E 0.00N" for reverse query at 0,0.',
    ];

    protected function createProvider(ClientInterface $httpClient)
    {
        return Photon::withKomootServer($httpClient);
    }

    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey(): string
    {
        return '';
    }
}
