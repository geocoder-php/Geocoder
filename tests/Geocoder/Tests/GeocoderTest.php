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

    public function testFromData()
    {
        $array = array(
            'latitude'  => 0.001,
            'longitude' => 1,
            'city'      => 'FOo CITY',
            'zipcode'   => '65943',
            'region'    => 'FOO region',
            'country'   => 'FOO Country'
        );

        $this->geocoder->fromArray($array);

        $this->assertEquals(0.001, $this->geocoder->getLatitude());
        $this->assertEquals(1, $this->geocoder->getLongitude());
        $this->assertEquals('Foo City', $this->geocoder->getCity());
        $this->assertEquals('65943', $this->geocoder->getZipcode());
        $this->assertEquals('Foo Region', $this->geocoder->getRegion());
        $this->assertEquals('Foo Country', $this->geocoder->getCountry());
    }

    public function testFromDataWithEmptyArray()
    {
        $this->geocoder->fromArray(array());

        $this->assertEquals('', $this->geocoder->getLatitude());
        $this->assertEquals('', $this->geocoder->getLongitude());
        $this->assertEquals('', $this->geocoder->getCity());
        $this->assertEquals('', $this->geocoder->getZipcode());
        $this->assertEquals('', $this->geocoder->getRegion());
        $this->assertEquals('', $this->geocoder->getCountry());
    }

    public function testFromDataWithNull()
    {
        $array = array(
            'latitude'  => 100,
            'longitude' => 1.2
        );

        $this->geocoder->fromArray($array);

        $this->assertEquals(100, $this->geocoder->getLatitude());
        $this->assertEquals(1.2, $this->geocoder->getLongitude());
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

    public function getGeocodedData($address)
    {
    }

    public function getReversedData(array $coordinates)
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

    public function fromArray(array $data = array())
    {
        return parent::fromArray($data);
    }
}
