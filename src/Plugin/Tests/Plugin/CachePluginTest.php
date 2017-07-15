<?php

declare(strict_types=1);

namespace Geocoder\Plugin\Tests\Plugin;

use Cache\Adapter\Void\VoidCachePool;
use Geocoder\Plugin\Plugin\CachePlugin;
use Geocoder\Plugin\Plugin\LimitPlugin;
use Geocoder\Plugin\Plugin\LocalePlugin;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\Query;
use League\Flysystem\Adapter\Local;
use PHPUnit\Framework\TestCase;

class CachePluginTest extends TestCase
{
    public function testPluginMiss()
    {
        $ttl = 4711;
        $query = GeocodeQuery::create('foo');
        $queryString = $query->__toString();
        $cache = $this->getMockBuilder(VoidCachePool::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'set'])
            ->getMock();

        $cache->expects($this->once())
            ->method('get')
            ->with('v4'.$queryString)
            ->willReturn(null);
        $cache->expects($this->once())
            ->method('set')
            ->with('v4'.$queryString, 'result', $ttl)
            ->willReturn(true);

        $first = function(Query $query) {
            $this->fail('Plugin should not restart the chain');
        };
        $next = function(Query $query) {
            return 'result';
        };

        $plugin = new CachePlugin($cache, $ttl);
        $this->assertEquals('result', $plugin->handleQuery($query, $next, $first));
    }
    public function testPluginHit()
    {
        $query = GeocodeQuery::create('foo');
        $queryString = $query->__toString();
        $cache = $this->getMockBuilder(VoidCachePool::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'set'])
            ->getMock();

        $cache->expects($this->once())
            ->method('get')
            ->with('v4'.$queryString)
            ->willReturn('result');
        $cache->expects($this->never())->method('set');

        $first = function(Query $query) {
            $this->fail('Plugin should not restart the chain');
        };
        $next = function(Query $query) {
            $this->fail('Plugin not call $next on cache hit');
        };

        $plugin = new CachePlugin($cache);
        $this->assertEquals('result', $plugin->handleQuery($query, $next, $first));
    }
}
