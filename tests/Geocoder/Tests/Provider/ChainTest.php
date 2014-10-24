<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Tests\TestCase;
use Geocoder\Exception\ChainNoResult;
use Geocoder\Provider\Chain;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class ChainTest extends TestCase
{
    public function testAdd()
    {
        $mock  = $this->getMock('Geocoder\Provider\Provider');
        $chain = new Chain();

        $chain->add($mock);
    }

    public function testGetName()
    {
        $chain = new Chain();
        $this->assertEquals('chain', $chain->getName());
    }

    public function testGetReversedData()
    {
        $mockOne = $this->getMock('Geocoder\\Provider\\Provider');
        $mockOne->expects($this->once())
            ->method('getReversedData')
            ->will($this->returnCallback(function () { throw new \Exception; }));

        $mockTwo = $this->getMock('Geocoder\\Provider\\Provider');
        $mockTwo->expects($this->once())
            ->method('getReversedData')
            ->with(array('11', '22'))
            ->will($this->returnValue(array('foo' => 'bar')));

        $chain = new Chain(array($mockOne, $mockTwo));

        $this->assertEquals(array('foo' => 'bar'), $chain->getReversedData(array('11', '22')));
    }

    public function testChainProviderReverseThrowsChainNoResult()
    {
        $mockOne = $this->getMock('Geocoder\\Provider\\Provider');
        $mockOne->expects($this->exactly(2))
            ->method('getReversedData')
            ->will($this->returnCallback(function () { throw new \Exception; }));

        $chain = new Chain(array($mockOne, $mockOne));

        try {
            $chain->getReversedData(array('11', '22'));
        } catch (ChainNoResult $e) {
            $this->assertCount(2, $e->getExceptions());
        }
    }

    public function testGetGeocodedData()
    {
        $mockOne = $this->getMock('Geocoder\\Provider\\Provider');
        $mockOne->expects($this->once())
            ->method('getGeocodedData')
            ->will($this->returnCallback(function () { throw new \Exception; }));

        $mockTwo = $this->getMock('Geocoder\\Provider\\Provider');
        $mockTwo->expects($this->once())
            ->method('getGeocodedData')
            ->with('Paris')
            ->will($this->returnValue(array('foo' => 'bar')));

        $chain = new Chain(array($mockOne, $mockTwo));

        $this->assertEquals(array('foo' => 'bar'), $chain->getGeocodedData('Paris'));
    }

    public function testChainProviderGeocodeThrowsChainNoResult()
    {
        $mockOne = $this->getMock('Geocoder\\Provider\\Provider');
        $mockOne->expects($this->exactly(2))
            ->method('getGeocodedData')
            ->will($this->returnCallback(function () { throw new \Exception; }));

        $chain = new Chain(array($mockOne, $mockOne));

        try {
            $chain->getGeocodedData('Paris');
        } catch (ChainNoResult $e) {
            $this->assertCount(2, $e->getExceptions());
        }
    }
}
