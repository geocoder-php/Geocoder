<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Provider\ChainProvider;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class ChainProviderTest extends TestCase
{
    public function testAddProvider()
    {
        $mock = $this->getMockProvider();

        $chain = new ChainProvider();
        $chain->addProvider($mock);
    }

    public function testGetName()
    {
        $chain = new ChainProvider();
        $this->assertEquals('chain', $chain->getName());
    }

    public function testGetReversedData()
    {
        $mockOne = $this->getMockProviderThrowException('getReversedData');
        $mockTwo = $this->getMockProvider('getReversedData', array('11', '22'));

        $chain = new ChainProvider(array($mockOne, $mockTwo));
        $this->assertEquals(array('foo' => 'bar'), $chain->getReversedData(array('11', '22')));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage No provider could provide the coordinated [55.699948,12.552839]
     */
    public function testGetReversedDataThrowNoResultException()
    {
        $mockOne = $this->getMockProviderThrowException('getReversedData');
        $mockTwo = $this->getMockProviderThrowException('getReversedData');

        $chain = new ChainProvider(array($mockOne, $mockTwo));
        $chain->getReversedData(array(55.699948, 12.552839));
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     */
    public function testGetReversedDataThrowInvalidCredentialsException()
    {
        $mockOne = $this->getMockProviderThrowException('getReversedData');
        $mockTwo = $this->getMockProviderThrowInvalidCredentialsException('getReversedData');

        $chain = new ChainProvider(array($mockOne, $mockTwo));
        $chain->getReversedData(array(55.699948, 12.552839));
    }

    public function testGetGeocodedData()
    {
        $mockOne = $this->getMockProviderThrowException('getGeocodedData');
        $mockTwo = $this->getMockProvider('getGeocodedData', 'Paris');

        $chain = new ChainProvider(array($mockOne, $mockTwo));
        $this->assertEquals(array('foo' => 'bar'), $chain->getGeocodedData('Paris'));
    }

    /**
     * @expectedException \Geocoder\Exception\NoResultException
     * @expectedExceptionMessage No provider could provide the address "København"
     */
    public function testGetGeocodedDataThrowNoResultException()
    {
        $mockOne = $this->getMockProviderThrowException('getGeocodedData');
        $mockTwo = $this->getMockProviderThrowException('getGeocodedData');

        $chain = new ChainProvider(array($mockOne, $mockTwo));
        $chain->getGeocodedData('København');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidCredentialsException
     *  @expectedExceptionMessage No API Key provided
     */
    public function testGetGeocodedDataThrowInvalidCredentialsException()
    {
        $mockOne = $this->getMockProviderThrowException('getGeocodedData');
        $mockTwo = $this->getMockProviderThrowInvalidCredentialsException('getGeocodedData');

        $chain = new ChainProvider(array($mockOne, $mockTwo));
        $chain->getGeocodedData('København');
    }
}
