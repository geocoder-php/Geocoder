<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Plugin\Tests\Plugin;

use Cache\Adapter\Void\VoidCachePool;
use Geocoder\Plugin\Plugin\CachePlugin;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\Query;
use PHPUnit\Framework\TestCase;

class CachePluginTest extends TestCase
{
    public function testPluginMiss()
    {
        $ttl = 4711;
        $query = GeocodeQuery::create('foo');
        $queryString = sha1($query->__toString());
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

        $first = function (Query $query) {
            $this->fail('Plugin should not restart the chain');
        };
        $next = function (Query $query) {
            return 'result';
        };

        $plugin = new CachePlugin($cache, $ttl);
        $this->assertEquals('result', $plugin->handleQuery($query, $next, $first));
    }

    public function testPluginHit()
    {
        $query = GeocodeQuery::create('foo');
        $queryString = sha1($query->__toString());
        $cache = $this->getMockBuilder(VoidCachePool::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'set'])
            ->getMock();

        $cache->expects($this->once())
            ->method('get')
            ->with('v4'.$queryString)
            ->willReturn('result');
        $cache->expects($this->never())->method('set');

        $first = function (Query $query) {
            $this->fail('Plugin should not restart the chain');
        };
        $next = function (Query $query) {
            $this->fail('Plugin not call $next on cache hit');
        };

        $plugin = new CachePlugin($cache);
        $this->assertEquals('result', $plugin->handleQuery($query, $next, $first));
    }
}
