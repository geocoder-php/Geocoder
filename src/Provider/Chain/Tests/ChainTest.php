<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Chain\Tests;

use Geocoder\Model\Query\GeocodeQuery;
use Geocoder\Model\Query\ReverseQuery;
use Geocoder\Provider\Provider;
use Geocoder\Tests\TestCase;
use Geocoder\Exception\ChainZeroResults;
use Geocoder\Provider\Chain\Chain;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class ChainTest extends TestCase
{
    public function testAdd()
    {
        $mock = $this->getMock('Geocoder\Provider\Provider');
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
        $mockOne = $this->getMock(Provider::class);
        $mockOne->expects($this->once())
            ->method('reverseQuery')
            ->will($this->returnCallback(function () {
                throw new \Exception();
            }));

        $mockTwo = $this->getMock('Geocoder\\Provider\\Provider');
        $mockTwo->expects($this->once())
            ->method('reverseQuery')
            ->will($this->returnValue(['foo' => 'bar']));

        $chain = new Chain([$mockOne, $mockTwo]);

        $this->assertEquals(['foo' => 'bar'], $chain->reverseQuery(ReverseQuery::fromCoordinates(11, 22)));
    }

    public function testReverseThrowsChainZeroResults()
    {
        $mockOne = $this->getMock('Geocoder\\Provider\\Provider');
        $mockOne->expects($this->exactly(2))
            ->method('reverseQuery')
            ->will($this->returnCallback(function () {
                throw new \Exception();
            }));

        $chain = new Chain([$mockOne, $mockOne]);

        try {
            $chain->reverseQuery(ReverseQuery::fromCoordinates(11, 22));
        } catch (ChainZeroResults $e) {
            $this->assertCount(2, $e->getExceptions());
        }
    }

    public function testGeocode()
    {
        $query = GeocodeQuery::create('Paris');
        $mockOne = $this->getMock('Geocoder\\Provider\\Provider');
        $mockOne->expects($this->once())
            ->method('geocodeQuery')
            ->will($this->returnCallback(function () {
                throw new \Exception();
            }));

        $mockTwo = $this->getMock('Geocoder\\Provider\\Provider');
        $mockTwo->expects($this->once())
            ->method('geocodeQuery')
            ->with($query)
            ->will($this->returnValue(['foo' => 'bar']));

        $chain = new Chain([$mockOne, $mockTwo]);

        $this->assertEquals(['foo' => 'bar'], $chain->geocodeQuery($query));
    }

    public function testGeocodeThrowsChainZeroResults()
    {
        $mockOne = $this->getMock('Geocoder\\Provider\\Provider');
        $mockOne->expects($this->exactly(2))
            ->method('geocodeQuery')
            ->will($this->returnCallback(function () {
                throw new \Exception();
            }));

        $chain = new Chain([$mockOne, $mockOne]);

        try {
            $chain->geocodeQuery(GeocodeQuery::create('Paris'));
        } catch (ChainZeroResults $e) {
            $this->assertCount(2, $e->getExceptions());
        }
    }
}
