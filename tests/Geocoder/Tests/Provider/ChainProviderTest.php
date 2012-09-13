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
        $mock = $this->getMock('Geocoder\\Provider\\ProviderInterface');

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
        $mockOne = $this->getMock('Geocoder\\Provider\\ProviderInterface');
        $mockOne->expects($this->once())
            ->method('getReversedData')
            ->will($this->returnCallback(function() { throw new \Exception; }));

        $mockTwo = $this->getMock('Geocoder\\Provider\\ProviderInterface');
        $mockTwo->expects($this->once())
            ->method('getReversedData')
            ->with(array('11', '22'))
            ->will($this->returnValue(array('foo' => 'bar')));

        $chain = new ChainProvider(array($mockOne, $mockTwo));

        $this->assertEquals(array('foo' => 'bar'), $chain->getReversedData(array('11', '22')));
    }

    public function testGetGeocodedData()
    {
        $mockOne = $this->getMock('Geocoder\\Provider\\ProviderInterface');
        $mockOne->expects($this->once())
            ->method('getGeocodedData')
            ->will($this->returnCallback(function() { throw new \Exception; }));

        $mockTwo = $this->getMock('Geocoder\\Provider\\ProviderInterface');
        $mockTwo->expects($this->once())
            ->method('getGeocodedData')
            ->with('Paris')
            ->will($this->returnValue(array('foo' => 'bar')));

        $chain = new ChainProvider(array($mockOne, $mockTwo));

        $this->assertEquals(array('foo' => 'bar'), $chain->getGeocodedData('Paris'));
    }
}
