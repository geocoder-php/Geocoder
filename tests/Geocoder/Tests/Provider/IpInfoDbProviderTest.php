<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;

use Geocoder\Provider\IpInfoDbProvider;

class IpInfoDbProviderTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testGetDataWithNullApiKey()
    {
        $provider = new IpInfoDbProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getData('foo');
    }

    public function testGetData()
    {
        $this->provider = new IpInfoDbProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getData('foobar');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    protected function getMockAdapter()
    {
        $mock = $this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface');
        $mock
            ->expects($this->once())
            ->method('getContent')
            ->will($this->returnArgument(0));

        return $mock;
    }
}
