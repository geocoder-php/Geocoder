<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Plugin\Tests;

use Geocoder\Model\AddressCollection;
use Geocoder\Plugin\Exception\LoopException;
use Geocoder\Plugin\Plugin;
use Geocoder\Plugin\PluginProvider;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\LookupQuery;
use Geocoder\Query\Query;
use Geocoder\Query\ReverseQuery;
use PHPUnit\Framework\TestCase;

class PluginProviderTest extends TestCase
{
    public function testDispatchQueries()
    {
        $geocodeQuery = GeocodeQuery::create('foo');
        $reverseQuery = ReverseQuery::fromCoordinates(47, 11);
        $lookupQuery = new LookupQuery('1');
        $collection = new AddressCollection([]);

        $provider = $this->getMockBuilder(Provider::class)
            ->disableOriginalConstructor()
            ->setMethods(['geocodeQuery', 'reverseQuery', 'lookupQuery', 'getName'])
            ->getMock();
        $provider->expects($this->once())
            ->method('geocodeQuery')
            ->with($geocodeQuery)
            ->willReturn($collection);
        $provider->expects($this->once())
            ->method('reverseQuery')
            ->with($reverseQuery)
            ->willReturn($collection);
        $provider->expects($this->once())
            ->method('lookupQuery')
            ->with($lookupQuery)
            ->willReturn($collection);
        $provider->expects($this->never())->method('getName');

        $pluginProvider = new PluginProvider($provider);
        $this->assertSame($collection, $pluginProvider->geocodeQuery($geocodeQuery));
        $this->assertSame($collection, $pluginProvider->reverseQuery($reverseQuery));
        $this->assertSame($collection, $pluginProvider->lookupQuery($lookupQuery));
    }

    public function testPluginsIsBeingUsedWhenGeocoding()
    {
        $geocodeQuery = GeocodeQuery::create('foo');
        $collection = new AddressCollection([]);

        $provider = $this->getMockBuilder(Provider::class)
            ->disableOriginalConstructor()
            ->setMethods(['geocodeQuery', 'reverseQuery', 'lookupQuery', 'getName'])
            ->getMock();
        $provider->expects($this->once())
            ->method('geocodeQuery')
            ->with($geocodeQuery)
            ->willReturn($collection);
        $provider->expects($this->never())->method('reverseQuery');
        $provider->expects($this->never())->method('lookupQuery');
        $provider->expects($this->never())->method('getName');

        $pluginA = $this->getMockBuilder(Plugin::class)
            ->disableOriginalConstructor()
            ->setMethods(['handleQuery'])
            ->getMock();
        $pluginA->expects($this->once())
            ->method('handleQuery')
            ->with($geocodeQuery, $this->isType('callable'), $this->isType('callable'))
            ->willReturnCallback(function (Query $query, callable $next, callable $first) {
                return $next($query);
            });

        $pluginProvider = new PluginProvider($provider, [$pluginA]);
        $this->assertSame($collection, $pluginProvider->geocodeQuery($geocodeQuery));
    }

    public function testPluginsIsBeingUsedWhenReverse()
    {
        $reverseQuery = ReverseQuery::fromCoordinates(47, 11);
        $collection = new AddressCollection([]);

        $provider = $this->getMockBuilder(Provider::class)
            ->disableOriginalConstructor()
            ->setMethods(['geocodeQuery', 'reverseQuery', 'lookupQuery', 'getName'])
            ->getMock();
        $provider->expects($this->never())->method('geocodeQuery');
        $provider->expects($this->never())->method('lookupQuery');
        $provider->expects($this->never())->method('getName');
        $provider->expects($this->once())
            ->method('reverseQuery')
            ->with($reverseQuery)
            ->willReturn($collection);

        $pluginA = $this->getMockBuilder(Plugin::class)
            ->disableOriginalConstructor()
            ->setMethods(['handleQuery'])
            ->getMock();
        $pluginA->expects($this->once())
            ->method('handleQuery')
            ->with($reverseQuery, $this->isType('callable'), $this->isType('callable'))
            ->willReturnCallback(function (Query $query, callable $next, callable $first) {
                return $next($query);
            });

        $pluginProvider = new PluginProvider($provider, [$pluginA]);
        $this->assertSame($collection, $pluginProvider->reverseQuery($reverseQuery));
    }

    public function testPluginsIsBeingUsedWhenLookup()
    {
        $lookupQuery = new LookupQuery('1');
        $collection = new AddressCollection([]);

        $provider = $this->getMockBuilder(Provider::class)
            ->disableOriginalConstructor()
            ->setMethods(['geocodeQuery', 'reverseQuery', 'lookupQuery', 'getName'])
            ->getMock();
        $provider->expects($this->never())->method('geocodeQuery');
        $provider->expects($this->never())->method('reverseQuery');
        $provider->expects($this->never())->method('getName');
        $provider->expects($this->once())
            ->method('lookupQuery')
            ->with($lookupQuery)
            ->willReturn($collection);

        $pluginA = $this->getMockBuilder(Plugin::class)
            ->disableOriginalConstructor()
            ->setMethods(['handleQuery'])
            ->getMock();
        $pluginA->expects($this->once())
            ->method('handleQuery')
            ->with($lookupQuery, $this->isType('callable'), $this->isType('callable'))
            ->willReturnCallback(function (Query $query, callable $next, callable $first) {
                return $next($query);
            });

        $pluginProvider = new PluginProvider($provider, [$pluginA]);
        $this->assertSame($collection, $pluginProvider->lookupQuery($lookupQuery));
    }

    public function testLoopException()
    {
        $this->expectException(LoopException::class);
        $geocodeQuery = GeocodeQuery::create('foo');

        $provider = $this->getMockBuilder(Provider::class)
            ->disableOriginalConstructor()
            ->setMethods(['geocodeQuery', 'reverseQuery', 'lookupQuery', 'getName'])
            ->getMock();
        $provider->expects($this->never())->method('geocodeQuery');
        $provider->expects($this->never())->method('reverseQuery');
        $provider->expects($this->never())->method('lookupQuery');
        $provider->expects($this->never())->method('getName');

        $pluginA = $this->getMockBuilder(Plugin::class)
            ->disableOriginalConstructor()
            ->setMethods(['handleQuery'])
            ->getMock();
        $pluginA->expects($this->any())
            ->method('handleQuery')
            ->with($geocodeQuery, $this->isType('callable'), $this->isType('callable'))
            ->willReturnCallback(function (Query $query, callable $next, callable $first) {
                return $next($query);
            });
        $pluginB = $this->getMockBuilder(Plugin::class)
            ->disableOriginalConstructor()
            ->setMethods(['handleQuery'])
            ->getMock();
        $pluginB->expects($this->any())
            ->method('handleQuery')
            ->with($geocodeQuery, $this->isType('callable'), $this->isType('callable'))
            ->willReturnCallback(function (Query $query, callable $next, callable $first) {
                return $first($query);
            });

        $pluginProvider = new PluginProvider($provider, [$pluginA, $pluginB]);
        $pluginProvider->geocodeQuery($geocodeQuery);
    }
}
