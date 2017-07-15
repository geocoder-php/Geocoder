<?php

declare(strict_types=1);

namespace Geocoder\Plugin\Tests\Plugin;

use Geocoder\Plugin\Plugin\LimitPlugin;
use Geocoder\Plugin\Plugin\LocalePlugin;
use Geocoder\Plugin\Plugin\QueryDataPlugin;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\Query;
use League\Flysystem\Adapter\Local;
use PHPUnit\Framework\TestCase;

class QueryDataPluginTest extends TestCase
{
    public function testPlugin()
    {
        $query = GeocodeQuery::create('xxx');
        $query = $query->withData('default', 'value');
        $first = function(Query $query) {
            $this->fail('Plugin should not restart the chain');
        };
        $next = function(Query $query) {
            $this->assertEquals('bar', $query->getData('foo'));
            $this->assertEquals('value', $query->getData('default'));
        };

        $plugin = new QueryDataPlugin(['foo'=>'bar', 'default'=>'new value']);
        $plugin->handleQuery($query, $next, $first);
    }

    public function testPluginForce()
    {
        $query = GeocodeQuery::create('xxx');
        $query = $query->withData('default', 'value');
        $first = function(Query $query) {
            $this->fail('Plugin should not restart the chain');
        };
        $next = function(Query $query) {
            $this->assertEquals('bar', $query->getData('foo'));
            $this->assertEquals('new value', $query->getData('default'));
        };

        $plugin = new QueryDataPlugin(['foo'=>'bar', 'default'=>'new value'], true);
        $plugin->handleQuery($query, $next, $first);
    }
}
