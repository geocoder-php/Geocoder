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

    public function testExtractData()
    {
        $array = array(
            'latitude'  => 'foo_latitude',
            'longitude' => 'foo_longitude',
            'city'      => 'foo_city',
            'zipcode'   => 'foo_zipcode',
            'region'    => 'foo_region',
            'country'   => 'foo_country'
        );

        $this->geocoder->extractData($array);

        $this->assertEquals('foo_latitude', $this->geocoder->getLatitude());
        $this->assertEquals('foo_longitude', $this->geocoder->getLongitude());
        $this->assertEquals('foo_city', $this->geocoder->getCity());
        $this->assertEquals('foo_zipcode', $this->geocoder->getZipcode());
        $this->assertEquals('foo_region', $this->geocoder->getRegion());
        $this->assertEquals('foo_country', $this->geocoder->getCountry());
    }

    public function testExtractDataWithEmptyArray()
    {
        $this->geocoder->extractData(array());

        $this->assertEquals('', $this->geocoder->getLatitude());
        $this->assertEquals('', $this->geocoder->getLongitude());
        $this->assertEquals('', $this->geocoder->getCity());
        $this->assertEquals('', $this->geocoder->getZipcode());
        $this->assertEquals('', $this->geocoder->getRegion());
        $this->assertEquals('', $this->geocoder->getCountry());
    }

    public function testExtractDataWithNull()
    {
        $array = array(
            'latitude'  => 'foo_latitude',
            'longitude' => 'foo_longitude',
        );

        $this->geocoder->extractData($array);

        $this->assertEquals('foo_latitude', $this->geocoder->getLatitude());
        $this->assertEquals('foo_longitude', $this->geocoder->getLongitude());
        $this->assertEquals('', $this->geocoder->getCity());
        $this->assertEquals('', $this->geocoder->getZipcode());
        $this->assertEquals('', $this->geocoder->getRegion());
        $this->assertEquals('', $this->geocoder->getCountry());
    }
}

class MockProvider implements ProviderInterface
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getData($value)
    {
    }

    public function getName()
    {
        return $this->name;
    }
}

class TestableGeocoder extends Geocoder
{
    public function getProvider()
    {
        return parent::getProvider();
    }

    public function extractData(array $data = array())
    {
        return parent::extractData($data);
    }
}
