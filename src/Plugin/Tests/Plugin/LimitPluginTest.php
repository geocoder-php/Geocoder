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

use Geocoder\Plugin\Plugin\LimitPlugin;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\Query;
use PHPUnit\Framework\TestCase;

class LimitPluginTest extends TestCase
{
    public function testPlugin(): void
    {
        $query = GeocodeQuery::create('foo');
        $first = function (Query $query) {
            $this->fail('Plugin should not restart the chain');
        };
        $next = function (Query $query) {
            $this->assertEquals(4711, $query->getLimit());
        };

        $plugin = new LimitPlugin(4711);
        $plugin->handleQuery($query, $next, $first);
    }
}
