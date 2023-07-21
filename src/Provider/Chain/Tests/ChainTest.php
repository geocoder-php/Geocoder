<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Chain\Tests;

use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Chain\Chain;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Nyholm\NSA;
use PHPUnit\Framework\TestCase;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class ChainTest extends TestCase
{
    public function testAdd(): void
    {
        $mock = $this->getMockBuilder('Geocoder\Provider\Provider')->getMock();
        $chain = new Chain();

        $chain->add($mock);
        $this->assertCount(1, NSA::getProperty($chain, 'providers'));
    }

    public function testGetName(): void
    {
        $chain = new Chain();
        $this->assertEquals('chain', $chain->getName());
    }

    public function testReverse(): void
    {
        $mockOne = $this->getMockBuilder(Provider::class)->getMock();
        $mockOne->expects($this->once())
            ->method('reverseQuery')
            ->will($this->returnCallback(function () {
                throw new \Exception();
            }));

        $mockTwo = $this->getMockBuilder('Geocoder\\Provider\\Provider')->getMock();
        $result = new AddressCollection(['foo' => 'bar']);
        $mockTwo->expects($this->once())
            ->method('reverseQuery')
            ->will($this->returnValue($result));

        $chain = new Chain([$mockOne, $mockTwo]);

        $this->assertEquals($result, $chain->reverseQuery(ReverseQuery::fromCoordinates(11, 22)));
    }

    public function testGeocode(): void
    {
        $query = GeocodeQuery::create('Paris');
        $mockOne = $this->getMockBuilder('Geocoder\\Provider\\Provider')->getMock();
        $mockOne->expects($this->once())
            ->method('geocodeQuery')
            ->will($this->returnCallback(function () {
                throw new \Exception();
            }));

        $mockTwo = $this->getMockBuilder('Geocoder\\Provider\\Provider')->getMock();
        $result = new AddressCollection(['foo' => 'bar']);
        $mockTwo->expects($this->once())
            ->method('geocodeQuery')
            ->with($query)
            ->will($this->returnValue($result));

        $chain = new Chain([$mockOne, $mockTwo]);

        $this->assertEquals($result, $chain->geocodeQuery($query));
    }
}
