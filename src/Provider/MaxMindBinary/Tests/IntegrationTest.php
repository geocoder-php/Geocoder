<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\MaxMindBinary\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\MaxMindBinary\MaxMindBinary;
use Psr\Http\Client\ClientInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected array $skippedTests = [];

    protected bool $testAddress = false;

    protected bool $testReverse = false;

    protected bool $testIpv6 = false;

    protected bool $testHttpProvider = false;

    public static function setUpBeforeClass(): void
    {
        if (false == function_exists('geoip_open')) {
            self::markTestSkipped('The maxmind\'s official lib required to run these tests.');
        }

        if (false == function_exists('GeoIP_record_by_addr')) {
            self::markTestSkipped('The maxmind\'s official lib required to run these tests.');
        }

        parent::setUpBeforeClass();
    }

    protected function createProvider(ClientInterface $httpClient)
    {
        return new MaxMindBinary(__DIR__.'/fixtures/GeoLiteCity.dat');
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
