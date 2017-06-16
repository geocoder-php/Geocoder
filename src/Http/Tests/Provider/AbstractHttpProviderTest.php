<?php

namespace Geocoder\Http\Provider\Tests;

use Geocoder\Http\Provider\AbstractHttpProvider;
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

class DummyProvider extends AbstractHttpProvider {
    public function getHttpClient()
    {
        return parent::getHttpClient();
    }

}
