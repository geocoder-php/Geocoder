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

use Geocoder\Exception\QuotaExceeded;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\Provider;
use Geocoder\Provider\Chain\Chain;
use Nyholm\NSA;
use PHPUnit\Framework\TestCase;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class ChainTest extends TestCase
{
    public function testAdd()
    {
        $mock = $this->getMockBuilder('Geocoder\Provider\Provider')->getMock();
        $chain = new Chain();

        $chain->add($mock);
        $this->assertCount(1, NSA::getProperty($chain, 'providers'));
    }

    public function testGetName()
    {
        $chain = new Chain();
        $this->assertEquals('chain', $chain->getName());
    }

    public function testReverse()
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

    public function testGeocode()
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

    public function testFetchingChainExceptions()
    {
        $query = GeocodeQuery::create('Paris');
        $mockOne = $this->getMockBuilder('Geocoder\\Provider\\Provider')->getMock();
        $mockOne->expects($this->once())
            ->method('geocodeQuery')
            ->will($this->returnCallback(function () {
                throw new \Exception('example exception 1');
            }));

        $mockTwo = $this->getMockBuilder('Geocoder\\Provider\\Provider')->getMock();
        $mockTwo->expects($this->once())
            ->method('geocodeQuery')
            ->will($this->returnCallback(function () {
                throw new QuotaExceeded('example exception 2');
            }));

        $mockThree = $this->getMockBuilder('Geocoder\\Provider\\Provider')->getMock();
        $result = new AddressCollection(['foo' => 'bar']);
        $mockThree->expects($this->once())
            ->method('geocodeQuery')
            ->with($query)
            ->will($this->returnValue($result));

        $chain = new Chain([$mockOne, $mockTwo, $mockThree]);

        $this->assertEquals($result, $chain->geocodeQuery($query));
        $this->assertCount(2, $chain->getPreviousQueryExceptions());
        $this->assertInstanceOf(\Exception::class, $chain->getPreviousQueryExceptions()[0]);
        $this->assertInstanceOf(QuotaExceeded::class, $chain->getPreviousQueryExceptions()[1]);
    }
}
