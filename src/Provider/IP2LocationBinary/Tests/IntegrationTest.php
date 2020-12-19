<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IP2LocationBinary\Tests;

use Geocoder\IntegrationTest\ProviderIntegrationTest;
use Geocoder\Provider\IP2LocationBinary\IP2LocationBinary;
use Http\Client\HttpClient;

/**
 * @author IP2Location <support@ip2location.com>
 */
class IntegrationTest extends ProviderIntegrationTest
{
    protected $skippedTests = [
    ];

    protected $testAddress = false;

    protected $testReverse = false;

    protected $testIpv6 = false;

    protected $testHttpProvider = false;

    public static function setUpBeforeClass(): void
    {
        if (false == class_exists('\\IP2Location\\Database')) {
            self::markTestSkipped('The IP2Location\'s official library required to run these tests.');
        }

        parent::setUpBeforeClass();
    }

    protected function createProvider(HttpClient $httpClient)
    {
        // Download this BIN database from https://lite.ip2location.com/database/ip-country-region-city-latitude-longitude-zipcode
        return new IP2LocationBinary(__DIR__.'/fixtures/IP2LOCATION-LITE-DB9.IPV6.BIN', \IP2Location\Database::FILE_IO);
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
