<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Mapzen\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\Mapzen\Mapzen;
use Http\Client\HttpClient;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected $skippedTests = [
        'testGeocodeQuery' => 'We get wrongs cords',
        'testReverseQueryWithNoResults' => 'We weirdly find stuff here...',
    ];

    protected $testIpv4 = false;
    protected $testIpv6 = false;

    protected function createProvider(HttpClient $httpClient)
    {
        return new Mapzen($httpClient, $this->getApiKey());
    }

    protected function getCacheDir()
    {
        return __DIR__.'/.cached_responses';
    }

    protected function getApiKey()
    {
        return $_SERVER['MAPZEN_API_KEY'];
    }
}
