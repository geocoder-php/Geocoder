<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\CacheProvider;
use Geocoder\Provider\FreeGeoIpProvider;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class GeocoderProviderTest extends TestCase
{

    protected function getCacheMock($expects = null)
    {
        if (null === $expects) {
            $expects = $this->once();
        }

        $cache = $this->getMock('\Geocoder\CacheAdapter\CacheInterface');
        $cache->expects($expects)
                ->method('retrieve')
                ->will($this->returnValue(null));
        return $cache;
    }

    public function testGetGeocodedDataWithNull()
    {
        $provider = new FreeGeoIpProvider($this->getMockAdapter());
        $cache = $this->getCacheMock();
        $this->provider = new CacheProvider($cache, $provider);
        
        $result = $this->provider->getGeocodedData(null);
        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetGeocodedDataWithEmpty()
    {
        $provider = new FreeGeoIpProvider($this->getMockAdapter());
        $cache = $this->getCacheMock();
        $this->provider = new CacheProvider($cache, $provider);
        
        $result = $this->provider->getGeocodedData('');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetGeocodedDataWithAddress()
    {
        $this->provider = new CacheProvider(
            $this->getCacheMock(),
            new \Geocoder\Provider\FreeGeoIpProvider(
                new \Geocoder\HttpAdapter\CurlHttpAdapter()
            )
        );
        $result = $this->provider->getGeocodedData('10 avenue Gambetta, Paris, France');

        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
        $this->assertNull($result['city']);
        $this->assertNull($result['zipcode']);
        $this->assertNull($result['region']);
        $this->assertNull($result['country']);
    }

    public function testGetGeocodedDataWithLocalhost()
    {
        $this->provider = new CacheProvider(
            $this->getCacheMock(),
            new \Geocoder\Provider\FreeGeoIpProvider(
                new \Geocoder\HttpAdapter\CurlHttpAdapter()
            )
        );

        $result = $this->provider->getGeocodedData('127.0.0.1');

        $this->assertArrayNotHasKey('latitude', $result);
        $this->assertArrayNotHasKey('longitude', $result);
        $this->assertArrayNotHasKey('zipcode', $result);

        $this->assertEquals('localhost', $result['city']);
        $this->assertEquals('localhost', $result['region']);
        $this->assertEquals('localhost', $result['country']);
    }

    public function testGetGeocodedDataWithRealIp()
    {
        $this->provider = new CacheProvider(
            $this->getCacheMock(),
            new \Geocoder\Provider\FreeGeoIpProvider(
                new \Geocoder\HttpAdapter\CurlHttpAdapter()
            )
        );
        
        $result = $this->provider->getGeocodedData('74.200.247.59');

        $this->assertEquals(33.0347, $result['latitude']);
        $this->assertEquals(-96.8134, $result['longitude']);
        $this->assertEquals(75093, $result['zipcode']);
        $this->assertEquals('Plano', $result['city']);
        $this->assertEquals('Texas', $result['region']);
        $this->assertEquals('United States', $result['country']);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     */
    public function testGetReverseData()
    {
        $this->provider = new FreeGeoIpProvider($this->getMockAdapter($this->never()));
        $this->provider->getReversedData(array(1, 2));
    }
}
