<?php

declare(strict_types=1);

/*
 * This file is part of the OpenGeocoding provider for Geocoder PHP.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\OpenGeocoding\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\OpenGeocoding\OpenGeocoding;
use Psr\Http\Client\ClientInterface;

class IntegrationTest extends ProviderIntegrationTest
{
    protected array $skippedTests = [
        'testReverseQueryWithNoResults' => 'The API returns a placeholder result at 0,0',
    ];

    protected bool $testIpv4 = false;

    protected bool $testIpv6 = false;

    protected function createProvider(ClientInterface $httpClient)
    {
        return new OpenGeocoding($httpClient, $this->getApiKey());
    }

    protected function getCacheDir(): string
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey(): string
    {
        return (string) getenv('OPENGEOCODING_API_KEY');
    }
}
