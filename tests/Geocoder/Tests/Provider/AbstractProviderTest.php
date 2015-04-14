<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;

use Geocoder\Provider\AbstractProvider;
use Ivory\HttpAdapter\AbstractHttpAdapter;
use Ivory\HttpAdapter\Message\InternalRequestInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class AbstractProviderTest extends TestCase
{
    public function testGetLocalhostDefaults()
    {
        $adapter  = new MockHttpAdapter();
        $provider = new MockProvider($adapter);
        $result   = $provider->getLocalhostDefaults();

        $this->assertEquals(2, count($result));
        $this->assertEquals('localhost', $result['locality']);
        $this->assertEquals('localhost', $result['country']);
    }
}

class MockProvider extends AbstractProvider
{
    public function getLocalhostDefaults()
    {
        return parent::getLocalhostDefaults();
    }
}

class MockHttpAdapter extends AbstractHttpAdapter
{
    public function getName()
    {
        return 'mock_http_adapter';
    }

    protected function sendInternalRequest(InternalRequestInterface $internalRequest)
    {
    }
}
