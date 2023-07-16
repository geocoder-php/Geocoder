<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Pelias\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\Pelias\Pelias;
use Psr\Http\Client\ClientInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected array $skippedTests = [
        'testGeocodeQuery' => 'No Pelias "default" instance.',
        'testGeocodeQueryWithNoResults' => 'No Pelias "default" instance.',
        'testReverseQuery' => 'No Pelias "default" instance.',
        'testReverseQueryWithNoResults' => 'No Pelias "default" instance.',
    ];

    protected bool $testIpv4 = false;

    protected bool $testIpv6 = false;

    protected function createProvider(ClientInterface $httpClient)
    {
        return new Pelias($httpClient, 'http://localhost/');
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
