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

        $this->mongo = new \Mongo;
        $this->database = 'geocoder_test';
        $this->collection = 'cache';
    }

    protected function tearDown()
    {
        $this->mongo->dropDB($this->database);
        unset($this->mongo);
    }

    /**
     * @dataProvider getCacheData
     */
    public function testRetrieveAndStore($key, $value)
    {
        $cache = new MongoDB($this->mongo, $this->database, $this->collection);
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
            array('foo', 2.1),
            array('bar', new \Geocoder\Result\Geocoded()),
        );
    }
}
