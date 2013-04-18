<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Provider\CacheProvider;
use Geocoder\Tests\TestCase;

class CacheProviderTest extends TestCase
{
    public function testCacheGeodedData()
    {
        $cacheId = 'geocoder_geocode_someid';
        $address = '10 avenue Gambetta, Paris, France';
        $result = array(
            'latitude' => 48.8630462,
            'longitude' => 2.3882487,
        );

        $provider = $this->getMock('Geocoder\\Provider\\ProviderInterface');
        $provider
            ->expects($this->once())
            ->method('getGeocodedData')
            ->with($address)
            ->will($this->returnValue($result))
        ;

        $cache = $this->getMock('Doctrine\Common\Cache\Cache');
        $cache
            ->expects($this->once())
            ->method('contains')
            ->will($this->returnValue(false))
        ;
        $cache
            ->expects($this->once())
            ->method('save')
            ->with($cacheId, $result)
        ;
        $cache
            ->expects($this->never())
            ->method('fetch')
        ;

        $cacheProvider = $this->getMock('Geocoder\Provider\CacheProvider', array('getCacheId'), array($cache, $provider));
        $cacheProvider
            ->expects($this->once())
            ->method('getCacheId')
            ->with($address)
            ->will($this->returnValue($cacheId))
        ;

        $this->assertEquals($result, $cacheProvider->getGeocodedData($address));
    }

    /**
     * @depends testCacheGeodedData
     */
    public function testFetchCachedGeocodedData()
    {
        $cacheId = 'geocoder_geocode_someid';
        $address = '10 avenue Gambetta, Paris, France';
        $result = array(
            'latitude' => 48.8630462,
            'longitude' => 2.3882487,
        );

        $provider = $this->getMock('Geocoder\\Provider\\ProviderInterface');
        $provider
            ->expects($this->never())
            ->method('getGeocodedData')
        ;

        $cache = $this->getMock('Doctrine\Common\Cache\Cache');
        $cache
            ->expects($this->once())
            ->method('contains')
            ->will($this->returnValue(true))
        ;
        $cache
            ->expects($this->never())
            ->method('save')
        ;
        $cache
            ->expects($this->once())
            ->method('fetch')
            ->with($cacheId)
            ->will($this->returnValue($result))
        ;

        $cacheProvider = $this->getMock('Geocoder\Provider\CacheProvider', array('getCacheId'), array($cache, $provider));
        $cacheProvider
            ->expects($this->once())
            ->method('getCacheId')
            ->with($address)
            ->will($this->returnValue($cacheId))
        ;

        $this->assertEquals($result, $cacheProvider->getGeocodedData($address));
    }

    public function testCacheReversedData()
    {
        $cacheId = 'geocoder_reverse_someid';
        $data = array(
            48.8630462,
            2.3882487,
        );
        $result = '10 avenue Gambetta, Paris, France';

        $provider = $this->getMock('Geocoder\\Provider\\ProviderInterface');
        $provider
            ->expects($this->once())
            ->method('getReversedData')
            ->with($data)
            ->will($this->returnValue($result))
        ;

        $cache = $this->getMock('Doctrine\Common\Cache\Cache');
        $cache
            ->expects($this->once())
            ->method('contains')
            ->will($this->returnValue(false))
        ;
        $cache
            ->expects($this->once())
            ->method('save')
            ->with($cacheId, $result)
        ;
        $cache
            ->expects($this->never())
            ->method('fetch')
        ;

        $cacheProvider = $this->getMock('Geocoder\Provider\CacheProvider', array('getCacheId'), array($cache, $provider));
        $cacheProvider
            ->expects($this->once())
            ->method('getCacheId')
            ->with($data)
            ->will($this->returnValue($cacheId))
        ;

        $this->assertEquals($result, $cacheProvider->getReversedData($data));
    }

    /**
     * @depends testCacheReversedData
     */
    public function testFetchCachedReversedData()
    {
        $cacheId = 'geocoder_reverse_someid';
        $data = array(
            48.8630462,
            2.3882487,
        );
        $result = '10 avenue Gambetta, Paris, France';

        $provider = $this->getMock('Geocoder\\Provider\\ProviderInterface');
        $provider
            ->expects($this->never())
            ->method('getReversedData')
        ;

        $cache = $this->getMock('Doctrine\Common\Cache\Cache');
        $cache
            ->expects($this->once())
            ->method('contains')
            ->will($this->returnValue(true))
        ;
        $cache
            ->expects($this->never())
            ->method('save')
        ;
        $cache
            ->expects($this->once())
            ->method('fetch')
            ->with($cacheId)
            ->will($this->returnValue($result))
        ;

        $cacheProvider = $this->getMock('Geocoder\Provider\CacheProvider', array('getCacheId'), array($cache, $provider));
        $cacheProvider
            ->expects($this->once())
            ->method('getCacheId')
            ->with($data)
            ->will($this->returnValue($cacheId))
        ;

        $this->assertEquals($result, $cacheProvider->getReversedData($data));
    }

    public function testGetCacheId()
    {
        $address = '10 avenue Gambetta, Paris, France';
        $reverse = array(
            48.8630462,
            2.3882487,
        );

        $provider = new CacheProvider($this->getMock('Doctrine\Common\Cache\Cache'), $this->getMock('Geocoder\\Provider\\ProviderInterface'));

        $this->assertStringStartsWith($provider->cachePrefix, $provider->getCacheId($address));
        $this->assertStringStartsWith($provider->cacheReversePrefix, $provider->getCacheId($reverse));
    }

    public function testMutateProvider()
    {
        $geoProvider = $this->getMock('Geocoder\\Provider\\ProviderInterface');

        $provider = new CacheProvider($this->getMock('Doctrine\Common\Cache\Cache'), $this->getMock('Geocoder\\Provider\\ProviderInterface'));
        $provider->setProvider($geoProvider);

        $this->assertSame($geoProvider, $provider->getProvider());
    }

    public function testCacheIsPubliclyRetrievable()
    {
        $cache = $this->getMock('Doctrine\Common\Cache\Cache');
        $provider = new CacheProvider($cache, $this->getMock('Geocoder\\Provider\\ProviderInterface'));

        $this->assertSame($cache, $provider->getCache());
    }

    public function testGetName()
    {
        $provider = new CacheProvider($this->getMock('Doctrine\Common\Cache\Cache'), $this->getMock('Geocoder\\Provider\\ProviderInterface'));

        $this->assertEquals('cache', $provider->getName());
    }
}
