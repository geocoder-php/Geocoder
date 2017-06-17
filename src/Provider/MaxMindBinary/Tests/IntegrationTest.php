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
use Http\Client\HttpClient;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected $skippedTests = [
    ];

    protected $testAddress = false;
    protected $testReverse = false;
    protected $testIpv6 = false;
    protected $testHttpProvider = false;

    public static function setUpBeforeClass()
    {
        if (false == function_exists('geoip_open')) {
            self::markTestSkipped('The maxmind\'s official lib required to run these tests.');
        }

        if (false == function_exists('GeoIP_record_by_addr')) {
            self::markTestSkipped('The maxmind\'s official lib required to run these tests.');
        }

        parent::setUpBeforeClass();
    }

    protected function createProvider(HttpClient $httpClient)
    {
        return new MaxMindBinary(__DIR__.'/fixtures/GeoLiteCity.dat');
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
