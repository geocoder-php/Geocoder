<?php

namespace Geocoder\Tests\CacheAdapter;

use Geocoder\Tests\TestCase;
use Geocoder\CacheAdapter\ApcAdapter;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class ApcAdapterTest extends TestCase
{
    protected function setUp()
    {
        if (!extension_loaded('apc')) {
            $this->markTestSkipped('Apc extension must be loaded');
        }
    }

    /**
     * @dataProvider getCacheData
     */
    public function testRetrieveAndStore($key, $value)
    {
        $cache = new ApcAdapter();
        $this->assertNull($cache->retrieve($key));
        $cache->store($key, $value);
        $this->assertEquals($value, $cache->retrieve($key));
    }

    static public function getCacheData()
    {
        return array(
            array('1', null),
            array('2', 'bar'),
            array('3', new \Geocoder\Result\Geocoded()),
            array('4', array('foo', 'bar')),
            array('5', 1),
            array('6', 2.1)
        );
    }
}
