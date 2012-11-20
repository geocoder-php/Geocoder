<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;

use Geocoder\HttpAdapter\HttpAdapterInterface;
use Geocoder\Provider\AbstractProvider;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class AbstractProviderTest extends TestCase
{
    public function testSetGetAdapter()
    {
        $adapter  = new MockHttpAdapter();
        $adapter2 = new MockHttpAdapter();
        $provider = new MockProvider($adapter);

        $this->assertSame($adapter, $provider->getAdapter());
        $this->assertNull($provider->getLocale());

        $provider->setAdapter($adapter2);
        $this->assertSame($adapter2, $provider->getAdapter());
    }

    public function testSetGetLocale()
    {
        $adapter  = new MockHttpAdapter();
        $provider = new MockProvider($adapter, 'fr_FR');

        $this->assertEquals('fr_FR', $provider->getLocale());

        $provider->setLocale('de_DE');
        $this->assertEquals('de_DE', $provider->getLocale());
    }

    public function testGetLocalhostDefaults()
    {
        $adapter  = new MockHttpAdapter();
        $provider = new MockProvider($adapter);
        $result   = $provider->getLocalhostDefaults();

        $this->assertEquals(4, count($result));
        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['county']);
        $this->assertEquals('localhost', $result['country']);
    }
}

class MockProvider extends AbstractProvider
{
    public function getAdapter()
    {
        return parent::getAdapter();
    }

    public function getLocale()
    {
        return parent::getLocale();
    }

    public function getLocalhostDefaults()
    {
        return parent::getLocalhostDefaults();
    }
}

class MockHttpAdapter implements HttpAdapterInterface
{
    public function getContent($url)
    {
    }

    public function getName()
    {
        return 'mock_http_adapter';
    }
}
