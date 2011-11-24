<?php

namespace Geocoder\Tests\CacheAdapter;

use Geocoder\Tests\TestCase;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
 
class MemcachedTest extends TestCase {

    protected function setUp()
    {
        if ( ! class_exists('Memcached', false) ) {
            $this->markTestSkipped('Memcached must be loaded');
        }
    }

    /**
     * @dataProvider getCacheData
     */
    public function testRetrieveAndStore($key, $value)
    {
        $cache = new \Geocoder\CacheAdapter\Memcached();
        $this->assertNull($cache->retrieve($key));
        $cache->store($key, $value);
        $this->assertEquals($value, $cache->retrieve($key));
    }

    static public function getCacheData()
    {
        return array(
            array('foo', null),
            array('foobar', 'bar'),
            array('bar', new \stdClass()),
            array('foo', array('foo', 'bar')),
            array('foo', 1),
            array('foo', 2.1)
        );
    }
}
