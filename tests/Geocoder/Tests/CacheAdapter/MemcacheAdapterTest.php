<?php

namespace Geocoder\Tests\CacheAdapter;

use Geocoder\Tests\TestCase;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class MemcacheAdapterTest extends TestCase
{
    protected function setUp()
    {
        if ( ! class_exists('\Memcache', false) ) {
            $this->markTestSkipped('Memcache extension must be loaded');
        }
    }

    /**
     * @dataProvider getCacheData
     */
    public function testRetrieveAndStore($key, $value)
    {
        $adapter = new \Memcache();
        $adapter->addserver('127.0.0.1');
        $cache = new \Geocoder\CacheAdapter\MemcacheAdapter($adapter);
        $this->assertNull($cache->retrieve($key));
        $cache->store($key, $value);
        $this->assertEquals($value, $cache->retrieve($key));
    }

    static public function getCacheData()
    {
        return array(
            array('1', null),
            array('2', 'bar'),
            array('3', new \stdClass()),
            array('4', array('foo', 'bar')),
            array('5', 1),
            array('6', 2.1)
        );
    }
}
