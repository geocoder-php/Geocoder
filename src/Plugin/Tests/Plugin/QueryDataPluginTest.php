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

use Geocoder\Plugin\Plugin\QueryDataPlugin;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\Query;
use PHPUnit\Framework\TestCase;

class QueryDataPluginTest extends TestCase
{
    public function testPlugin(): void
    {
        $query = GeocodeQuery::create('xxx');
        $query = $query->withData('default', 'value');
        $first = function (Query $query) {
            $this->fail('Plugin should not restart the chain');
        };
        $next = function (Query $query) {
            $this->assertEquals('bar', $query->getData('foo'));
            $this->assertEquals('value', $query->getData('default'));
        };

        $plugin = new QueryDataPlugin(['foo' => 'bar', 'default' => 'new value']);
        $plugin->handleQuery($query, $next, $first);
    }

    public function testPluginForce(): void
    {
        $query = GeocodeQuery::create('xxx');
        $query = $query->withData('default', 'value');
        $first = function (Query $query) {
            $this->fail('Plugin should not restart the chain');
        };
        $next = function (Query $query) {
            $this->assertEquals('bar', $query->getData('foo'));
            $this->assertEquals('new value', $query->getData('default'));
        };

        $plugin = new QueryDataPlugin(['foo' => 'bar', 'default' => 'new value'], true);
        $plugin->handleQuery($query, $next, $first);
    }
}
