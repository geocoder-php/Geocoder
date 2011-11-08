<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;

use Geocoder\Provider\YahooProvider;

class YahooProviderTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testGetGeocodedDataWithNullApiKey()
    {
        $provider = new YahooProvider($this->getMock('\Geocoder\HttpAdapter\HttpAdapterInterface'), null);
        $provider->getGeocodedData('foo');
    }

    public function testGetGeocodedData()
    {
        $this->provider = new YahooProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getGeocodedData('foobar');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetReversedData()
    {
        $this->provider = new YahooProvider($this->getMockAdapter(), 'api_key');
        $result = $this->provider->getReversedData(array(1, 2));

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
