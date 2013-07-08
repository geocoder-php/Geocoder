<?php

namespace Geocoder\Tests;

use Geocoder\Geocoder;
use Geocoder\Provider\ProviderInterface;
use Geocoder\Result\Geocoded;

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
        $provider1 = new MockProvider('test1');
        $provider2 = new MockProvider('test2');
        $provider3 = new MockProvider('test3');
        $this->geocoder->registerProviders(array($provider1, $provider2, $provider3));

        $this->assertSame($provider1, $this->geocoder->getProvider());
    }

    public function testGeocodeReturnsInstanceOfResultInterface()
    {
        $this->geocoder->registerProvider(new MockProvider('test1'));
        $this->assertInstanceOf('Geocoder\Result\ResultInterface', $this->geocoder->geocode('foobar'));
        $this->assertInstanceOf('Geocoder\Result\Geocoded', $this->geocoder->geocode('foobar'));
    }

    public function testGeocodeEmpty()
    {
        $this->geocoder->registerProvider(new MockProviderWithRequestCount('test2'));
        $this->assertEmptyResult($this->geocoder->geocode(''));
        $this->assertEquals(0, $this->geocoder->getProvider('test2')->geocodeCount);
        $this->assertEmptyResult($this->geocoder->geocode(null));
        $this->assertEquals(0, $this->geocoder->getProvider('test2')->geocodeCount);
    }

    public function testReverseReturnsInstanceOfResultInterface()
    {
        $this->geocoder->registerProvider(new MockProvider('test1'));
        $this->assertInstanceOf('Geocoder\Result\ResultInterface', $this->geocoder->reverse(1, 2));
        $this->assertInstanceOf('Geocoder\Result\Geocoded', $this->geocoder->reverse(1, 2));
    }

    public function testReverseEmpty()
    {
        $this->geocoder->registerProvider(new MockProviderWithRequestCount('test2'));
        $this->assertEmptyResult($this->geocoder->reverse('', ''));
        $this->assertEquals(0, $this->geocoder->getProvider('test2')->geocodeCount);
        $this->assertEmptyResult($this->geocoder->reverse(null, null));
        $this->assertEquals(0, $this->geocoder->getProvider('test2')->geocodeCount);
    }

    public function testUseCustomDefaultResultFactory()
    {
        $factoryMock = $this->getMock('Geocoder\Result\DefaultResultFactory');
        $factoryMock
            ->expects($this->once())
            ->method('newInstance')
            ->will($this->returnValue(new DummyResult()));

        $geocoder = new TestableGeocoder(null, $factoryMock);
        $this->assertInstanceOf('Geocoder\Tests\DummyResult', $geocoder->returnResult(array()));
    }

    public function testSetAndUseCustomDefaultResultFactory()
    {
        $factoryMock = $this->getMock('Geocoder\Result\DefaultResultFactory');
        $factoryMock
            ->expects($this->once())
            ->method('newInstance')
            ->will($this->returnValue(new DummyResult()));

        $geocoder = new TestableGeocoder();
        $geocoder->setResultFactory($factoryMock);
        $this->assertInstanceOf('Geocoder\Tests\DummyResult', $geocoder->returnResult(array()));
    }

    public function testUseCustomMultipleResultFactory()
    {
        $factoryMock = $this->getMock('Geocoder\Result\MultipleResultFactory');
        $factoryMock->expects($this->at(0))->method('newInstance')->will($this->returnValue(new DummyResult()));
        $factoryMock->expects($this->at(1))->method('newInstance')->will($this->returnValue(new DummyResult()));
        $factoryMock->expects($this->at(2))->method('newInstance')->will($this->returnValue(new DummyResult()));

        $geocoder = new TestableGeocoder(null, $factoryMock);
        $results = $geocoder->returnResult(array(
            array(),
            array(),
            array(),
        ));

        $this->assertInstanceOf('\SplObjectStorage', $results);
        $this->assertCount(3, $results);
        foreach ($results as $result) {
            $this->assertInstanceOf('Geocoder\Tests\DummyResult', $result);
            $this->assertInstanceOf('Geocoder\Result\ResultInterface', $result);
        }
    }

    public function testSetAndUseCustomMultipleResultFactory()
    {
        $factoryMock = $this->getMock('Geocoder\Result\MultipleResultFactory');
        $factoryMock->expects($this->at(0))->method('newInstance')->will($this->returnValue(new DummyResult()));
        $factoryMock->expects($this->at(1))->method('newInstance')->will($this->returnValue(new DummyResult()));
        $factoryMock->expects($this->at(2))->method('newInstance')->will($this->returnValue(new DummyResult()));

        $geocoder = new TestableGeocoder();
        $geocoder->setResultFactory($factoryMock);
        $results = $geocoder->returnResult(array(
            array(),
            array(),
            array(),
        ));

        $this->assertInstanceOf('\SplObjectStorage', $results);
        $this->assertCount(3, $results);
        foreach ($results as $result) {
            $this->assertInstanceOf('Geocoder\Tests\DummyResult', $result);
            $this->assertInstanceOf('Geocoder\Result\ResultInterface', $result);
        }
    }

    public function testSetMaxResults()
    {
        $this->geocoder->limit(3);
        $this->assertSame(3, $this->geocoder->getMaxResults());
    }

    public function testDefaultMaxResults()
    {
        $this->assertSame(Geocoder::MAX_RESULTS, $this->geocoder->getMaxResults());
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

class TestableGeocoder extends Geocoder
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

class DummyResult extends Geocoded
{
}
