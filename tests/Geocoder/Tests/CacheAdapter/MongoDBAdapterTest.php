<?php

namespace Geocoder\Tests\CacheAdapter;

use Geocoder\CacheAdapter\MongoDBAdapter;
use Geocoder\Tests\TestCase;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
 
class MongoDBAdapterTest extends TestCase
{

    protected function setUp()
    {
        if ( ! class_exists('\Mongo') ) {
            $this->markTestIncomplete('The mongo extension must be loaded');
        }
    }

    protected function tearDown()
    {
        $mongo = new \Mongo;
        $mongo->dropDB('test');
    }

    /**
     * @dataProvider getCacheData
     */
    public function testRetrieveAndStore($key, $value)
    {
        $cache = new MongoDBAdapter(new \Mongo, 'test', 'cache');
        $this->assertNull($cache->retrieve($key));
        $cache->store($key, $value);
        $this->assertEquals($value, $cache->retrieve($key));
    }
    
    static public function getCacheData()
    {
        return array(
            array('foo', null),
            array('foobar', 'bar'),
            array('foo', array('foo', 'bar')),
            array('foo', 1),
            array('foo', 2.1)
        );
    }
}
