<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Http\Provider\Tests;

use Geocoder\Http\Provider\AbstractHttpProvider;
use Http\Client\HttpClient;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;

class AbstractHttpProviderTest extends TestCase
{
    public function testHttpClientGetter()
    {
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $provider = new DummyProvider($client);
        $this->assertSame($client, $provider->getHttpClient());
    }
}

class DummyProvider extends AbstractHttpProvider
{
    public function getHttpClient(): HttpClient
    {
        return parent::getHttpClient();
    }
}
