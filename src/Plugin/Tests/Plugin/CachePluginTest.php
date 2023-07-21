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
use Geocoder\Model\Coordinates;
use Geocoder\Plugin\Plugin\CachePlugin;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\Query;
use Geocoder\Query\ReverseQuery;
use PHPUnit\Framework\TestCase;

class CachePluginTest extends TestCase
{
    public function testPluginMiss(): void
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

    public function getQueryProvider(): \Generator
    {
        $query = GeocodeQuery::create('foo');
        $key = sha1($query->__toString());
        yield [$query, $key];

        $query = ReverseQuery::create(new Coordinates(12.123456, 12.123456));
        $lessPreciseQuery = $query->withCoordinates(new Coordinates(12.1235, 12.1235));
        $key = sha1((string) $lessPreciseQuery);
        yield [$query, $key];
    }

    /**
     * @dataProvider getQueryProvider
     */
    public function testPluginHit(Query $query, string $key): void
    {
        $cache = $this->getMockBuilder(VoidCachePool::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'set'])
            ->getMock();

        $cache->expects($this->once())
            ->method('get')
            ->with('v4'.$key)
            ->willReturn('result');
        $cache->expects($this->never())->method('set');

        $first = function (Query $query) {
            $this->fail('Plugin should not restart the chain');
        };
        $next = function (Query $query) {
            $this->fail('Plugin not call $next on cache hit');
        };

        $plugin = new CachePlugin($cache, 0, 4);
        $this->assertEquals('result', $plugin->handleQuery($query, $next, $first));
    }
}
