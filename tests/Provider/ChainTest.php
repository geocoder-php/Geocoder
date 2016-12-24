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

    public function testReverse()
    {
        $mockOne = $this->getMock('Geocoder\\Provider\\Provider');
        $mockOne->expects($this->once())
            ->method('reverse')
            ->will($this->returnCallback(function () { throw new \Exception; }));

        $mockTwo = $this->getMock('Geocoder\\Provider\\Provider');
        $mockTwo->expects($this->once())
            ->method('reverse')
            ->with('11', '22')
            ->will($this->returnValue(array('foo' => 'bar')));

        $chain = new Chain(array($mockOne, $mockTwo));

        $this->assertEquals(array('foo' => 'bar'), $chain->reverse('11', '22'));
    }

    public function testReverseThrowsChainNoResult()
    {
        $mockOne = $this->getMock('Geocoder\\Provider\\Provider');
        $mockOne->expects($this->exactly(2))
            ->method('reverse')
            ->will($this->returnCallback(function () { throw new \Exception; }));

        $chain = new Chain(array($mockOne, $mockOne));

        try {
            $chain->reverse('11', '22');
        } catch (ChainNoResult $e) {
            $this->assertCount(2, $e->getExceptions());
        }
    }

    public function testGeocode()
    {
        $mockOne = $this->getMock('Geocoder\\Provider\\Provider');
        $mockOne->expects($this->once())
            ->method('geocode')
            ->will($this->returnCallback(function () { throw new \Exception; }));

        $mockTwo = $this->getMock('Geocoder\\Provider\\Provider');
        $mockTwo->expects($this->once())
            ->method('geocode')
            ->with('Paris')
            ->will($this->returnValue(array('foo' => 'bar')));

        $chain = new Chain(array($mockOne, $mockTwo));

        $this->assertEquals(array('foo' => 'bar'), $chain->geocode('Paris'));
    }

    public function testGeocodeThrowsChainNoResult()
    {
        $mockOne = $this->getMock('Geocoder\\Provider\\Provider');
        $mockOne->expects($this->exactly(2))
            ->method('geocode')
            ->will($this->returnCallback(function () { throw new \Exception; }));

        $chain = new Chain(array($mockOne, $mockOne));

        try {
            $chain->geocode('Paris');
        } catch (ChainNoResult $e) {
            $this->assertCount(2, $e->getExceptions());
        }
    }
}
