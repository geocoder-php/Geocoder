<?php

namespace Geocoder\Tests;

use Geocoder\ProviderBasedGeocoder;
use Geocoder\Provider\Provider;
use Geocoder\Model\Address;
use Geocoder\Model\AddressFactory;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class ProviderBasedGeocoderTest extends TestCase
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
    }

    /**
     * @expectedException \Geocoder\Exception\ProviderNotRegistered
     */
    public function testUsingNonExistantProviderShouldThrowAnException()
    {
        $this->geocoder->using('non_existant');
    }

    /**
     * @expectedException \Geocoder\Exception\ProviderNotRegistered
     */
    public function testUsingNullShouldThrowAnException()
    {
        $this->geocoder->using(null);
    }

    /**
     * @expectedException \Geocoder\Exception\ProviderNotRegistered
     */
    public function testUsingAnEmptyProviderNameShouldThrowAnException()
    {
        $this->geocoder->using('');
    }

    public function testGetProviders()
    {
        $provider1 = new MockProvider('test1');
        $provider2 = new MockProvider('test2');

        $this->geocoder->registerProviders(array($provider1, $provider2));
        $result = $this->geocoder->getProviders();

        $expected = array(
            'test1' => $provider1,
            'test2' => $provider2
        );

        $this->assertSame($expected, $result);
        $this->assertArrayHasKey('test1', $result);
        $this->assertArrayHasKey('test2', $result);
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
        $this->geocoder->registerProviders(array(
            $provider1 = new MockProvider('test1'),
            $provider2 = new MockProvider('test2'),
            $provider3 = new MockProvider('test3'),
        ));

        $this->assertSame($provider1, $this->geocoder->getProvider());
    }

    public function testGeocodeAlwaysReturnsArrayAndDoesNotCallProviderWithEmptyValues()
    {
        $this->geocoder->registerProvider(new MockProviderWithRequestCount('test2'));

        $this->assertEmpty($this->geocoder->geocode(''));
        $this->assertEquals(0, $this->geocoder->getProvider('test2')->geocodeCount);

        $this->assertEmpty($this->geocoder->geocode(null));
        $this->assertEquals(0, $this->geocoder->getProvider('test2')->geocodeCount);
    }

    public function testReverseReturnsArray()
    {
        $this->geocoder->registerProvider(new MockProvider('test1'));

        $this->assertTrue(is_array($this->geocoder->reverse(1, 2)));
    }

    public function testReverseAlwaysReturnsArrayAndDoesNotCallProviderWihEmptyValues()
    {
        $this->geocoder->registerProvider(new MockProviderWithRequestCount('test2'));

        $this->assertEmpty($this->geocoder->reverse('', ''));
        $this->assertEquals(0, $this->geocoder->getProvider('test2')->geocodeCount);

        $this->assertEmpty($this->geocoder->reverse(null, null));
        $this->assertEquals(0, $this->geocoder->getProvider('test2')->geocodeCount);
    }

    public function testSetMaxResults()
    {
        $this->geocoder->limit(3);
        $this->assertSame(3, $this->geocoder->getMaxResults());
    }

    public function testDefaultMaxResults()
    {
        $this->assertSame(ProviderBasedGeocoder::MAX_RESULTS, $this->geocoder->getMaxResults());
    }

    private function getAddressMock()
    {
        return (new AddressFactory())->createFromArray([]);
    }
}

class MockProvider implements Provider
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

    public function setMaxResults($maxResults)
    {
        return $this;
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

class TestableGeocoder extends ProviderBasedGeocoder
{
    public $countCallGetProvider = 0;

    public function getProvider()
    {
        $this->countCallGetProvider++;

        return parent::getProvider();
    }

    public function returnResult(array $data = array())
    {
        return parent::returnResult($data);
    }
}
