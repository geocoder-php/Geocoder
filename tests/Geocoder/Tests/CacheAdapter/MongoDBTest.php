<?php

namespace Geocoder\Tests\CacheAdapter;

use Geocoder\CacheAdapter\MongoDB;
use Geocoder\Tests\TestCase;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class MongoDBTest extends TestCase
{
    protected function setUp()
    {
        if (!class_exists('\Mongo')) {
            $this->markTestSkipped('The mongo extension must be loaded');
        }
    }

    /**
     * @dataProvider getCacheData
     */
    public function testRetrieveAndStore($key, $value)
    {
        $mongo = new \Mongo();

        $cache = new MongoDB($mongo, 'geocoder_test', 'cache');
        $this->assertNull($cache->retrieve($key));
        $cache->store($key, $value);
        $this->assertEquals($value, $cache->retrieve($key));

        $mongo->dropDB('geocoder_test');
    }
    
    static public function getCacheData()
    {
        return array(
            array('foo', null),
            array('foobar', 'bar'),
            array('foo', array('foo', 'bar')),
            array('foo', 1),
            array('foo', 2.1),
            array('bar', new \Geocoder\Result\Geocoded()),
        );
    }
}
