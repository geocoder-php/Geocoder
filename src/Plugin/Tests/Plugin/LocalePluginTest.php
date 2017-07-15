<?php

declare(strict_types=1);

namespace Geocoder\Plugin\Tests\Plugin;

use Geocoder\Plugin\Plugin\LimitPlugin;
use Geocoder\Plugin\Plugin\LocalePlugin;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\Query;
use League\Flysystem\Adapter\Local;
use PHPUnit\Framework\TestCase;

class LocalePluginTest extends TestCase
{
    public function testPlugin()
    {
        $query = GeocodeQuery::create('foo');
        $first = function(Query $query) {
            $this->fail('Plugin should not restart the chain');
        };
        $next = function(Query $query) {
            $this->assertEquals('sv', $query->getLocale());
        };

        $plugin = new LocalePlugin('sv');
        $plugin->handleQuery($query, $next, $first);
    }
}
