<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\ArcGISOnlineTest\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\ArcGISOnline\ArcGISOnline;
use Psr\Http\Client\ClientInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected bool $testIpv4 = false;

    protected bool $testIpv6 = false;

    protected array $skippedTests = [
        'testReverseQueryWithNoResults' => 'ArcGIS REST API returns "אצטדיון כדורגל עירוני" for reverse query at 0,0.',
    ];

    protected function createProvider(ClientInterface $httpClient)
    {
        return new ArcGISOnline($httpClient);
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
