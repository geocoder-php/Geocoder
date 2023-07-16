<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Geonames\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\Geonames\Geonames;
use Psr\Http\Client\ClientInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected array $skippedTests = [
        'testGeocodeQuery' => 'This address is not found..',
    ];

    protected bool $testIpv4 = false;

    protected bool $testIpv6 = false;

    protected function createProvider(ClientInterface $httpClient)
    {
        return new Geonames($httpClient, $this->getApiKey());
    }

    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey(): string
    {
        return $_SERVER['GEONAMES_USERNAME'];
    }
}
