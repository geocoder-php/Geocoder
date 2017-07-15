<?php

declare(strict_types=1);

namespace Geocoder\Plugin\Tests\Plugin;

use Geocoder\Model\Bounds;
use Geocoder\Plugin\Plugin\BoundsPlugin;
use Geocoder\Plugin\Plugin\LimitPlugin;
use Geocoder\Plugin\Plugin\LocalePlugin;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\Query;
use Geocoder\Query\ReverseQuery;
use League\Flysystem\Adapter\Local;
use PHPUnit\Framework\TestCase;

class BoundsPluginTest extends TestCase
{
    public function testGeocode()
    {
        $bounds = new Bounds(4,7,1,1);
        $query = GeocodeQuery::create('foo');
        $first = function(Query $query) {
            $this->fail('Plugin should not restart the chain');
        };
        $next = function(GeocodeQuery $query) use ($bounds) {
            $this->assertEquals($bounds, $query->getBounds());
        };

        $plugin = new BoundsPlugin($bounds);
        $plugin->handleQuery($query, $next, $first);
    }

    public function testReverse()
    {
        $bounds = new Bounds(4,7,1,1);
        $query = ReverseQuery::fromCoordinates(71,11);
        $first = function(Query $query) {
            $this->fail('Plugin should not restart the chain');
        };
        $next = function(Query $query) use ($bounds) {
            $this->assertTrue(true, 'We should not fail on ReverseQuery');
        };

        $plugin = new BoundsPlugin($bounds);
        $plugin->handleQuery($query, $next, $first);
    }
}
