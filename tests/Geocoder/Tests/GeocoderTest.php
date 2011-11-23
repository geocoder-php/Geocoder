<?php

namespace Geocoder\Tests;

use Geocoder\Geocoder;
use Geocoder\Provider\ProviderInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class GeocoderTest extends TestCase
{
    protected $geocoder;

    protected function setUp()
    {
        $this->geocoder = new TestableGeocoder();
    }

    public function testRegisterProvider()
    {
        $provider = new MockProvider('test');
        $this->geocoder->registerProvider($provider);

        $this->assertSame($provider, $this->geocoder->getProvider());
    }

    public function testRegisterProviders()
    {
        $provider = new MockProvider('test');
        $this->geocoder->registerProviders(array($provider));

        $this->assertSame($provider, $this->geocoder->getProvider());
    }

    public function testUsing()
    {
        $provider1 = new MockProvider('test1');
        $provider2 = new MockProvider('test2');
        $this->geocoder->registerProviders(array($provider1, $provider2));

        $this->assertSame($provider1, $this->geocoder->getProvider());

        $this->geocoder->using('test1');
        $this->assertSame($provider1, $this->geocoder->getProvider());

        $this->geocoder->using('test2');
        $this->assertSame($provider2, $this->geocoder->getProvider());

        $this->geocoder->using('test1');
        $this->assertSame($provider1, $this->geocoder->getProvider());

        $this->geocoder->using('non_existant');
        $this->assertSame($provider1, $this->geocoder->getProvider());

        $this->geocoder->using(null);
        $this->assertSame($provider1, $this->geocoder->getProvider());

        $this->geocoder->using('');
        $this->assertSame($provider1, $this->geocoder->getProvider());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetProvider()
    {
        $this->geocoder->getProvider();
        $this->fail('getProvider() should throw an exception');
    }

    public function testGetProviderWithMultipleProvidersReturnsTheFirstOne()
    {
        $provider1 = new MockProvider('test1');
        $provider2 = new MockProvider('test2');
        $provider3 = new MockProvider('test3');
        $this->geocoder->registerProviders(array($provider1, $provider2, $provider3));

        $this->assertSame($provider1, $this->geocoder->getProvider());
    }

    public function testGeocodeReturnsInstanceOfGeocoded()
    {
        $this->geocoder->registerProvider(new MockProvider('test1'));
        $this->assertInstanceOf('\Geocoder\Result\Geocoded', $this->geocoder->geocode('foobar'));
    }

    public function testGeocodeWithCache()
    {
        $cache = new IntrospectableInMemory();

        $this->geocoder->registerProvider(new MockProviderWithData('test1'));
        $this->geocoder->registerCache($cache);

        $result = $this->geocoder->geocode('hello, world');

        $this->assertEquals(1, $this->geocoder->countCallGetProvider);

        $store  = $cache->getStore();

        $this->assertEquals(1, count($store));
        $this->assertArrayHasKey(sha1('hello, world'), $store);
        $this->assertSame($result, $store[sha1('hello, world')]);

        $result = $this->geocoder->geocode('hello, world');

        $this->assertEquals(1, $this->geocoder->countCallGetProvider);
        $this->assertEquals(1, count($store));
    }

    public function testReverseWithCache()
    {
        $cache = new IntrospectableInMemory();

        $this->geocoder->registerProvider(new MockProviderWithData('test1'));
        $this->geocoder->registerCache($cache);

        $result = $this->geocoder->reverse('foo', 'bar');

        $this->assertEquals(1, $this->geocoder->countCallGetProvider);

        $store  = $cache->getStore();

        $this->assertEquals(1, count($store));
        $this->assertArrayHasKey(sha1('foo-bar'), $store);
        $this->assertSame($result, $store[sha1('foo-bar')]);

        $result = $this->geocoder->reverse('foo', 'bar');

        $this->assertEquals(1, $this->geocoder->countCallGetProvider);
        $this->assertEquals(1, count($store));
    }

    public function testEmpty()
    {
        $this->geocoder->registerProvider(new MockProviderWithRequestCount('test2'));
        $this->assertEmptyResult($this->geocoder->geocode(''));
        $this->assertEquals(0, $this->geocoder->getProvider('test2')->geocodeCount);
        $this->assertEmptyResult($this->geocoder->geocode(null));
        $this->assertEquals(0, $this->geocoder->getProvider('test2')->geocodeCount);
    }

    protected function assertEmptyResult($result)
    {
        $this->assertEquals(0, $result->getLatitude());
        $this->assertEquals(0, $result->getLongitude());
        $this->assertEquals('', $result->getCity());
        $this->assertEquals('', $result->getZipcode());
        $this->assertEquals('', $result->getRegion());
        $this->assertEquals('', $result->getCountry());
    }
}

class MockProvider implements ProviderInterface
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getGeocodedData($address)
    {
        return array();
    }

    public function getReversedData(array $coordinates)
    {
        return array();
    }

    public function getName()
    {
        return $this->name;
    }
}

class MockProviderWithData extends MockProvider
{
    public function getGeocodedData($address)
    {
        return array(
            'latitude' => 123,
            'longitude' => 456
        );
    }
}

class MockProviderWithRequestCount extends MockProvider
{
    public $geocodeCount = 0;

    public function getGeocodedData($address)
    {
        $this->geocodeCount++;
    }
}

class TestableGeocoder extends Geocoder
{
    public $countCallGetProvider = 0;

    public function getProvider()
    {
        $this->countCallGetProvider++;
        return parent::getProvider();
    }
}

class IntrospectableInMemory extends \Geocoder\CacheAdapter\InMemory
{
    public function getStore()
    {
        return $this->store;
    }
}
