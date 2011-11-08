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
        $provider->getGeocodedData('foo');
    }

    public function testGetGeocodedData()
    {
        $this->provider = new IpInfoDbProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('foobar');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testReversedData()
    {
        $this->provider = new IpInfoDbProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), 'api_key');
        $result = $this->provider->getReversedData(array());
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
