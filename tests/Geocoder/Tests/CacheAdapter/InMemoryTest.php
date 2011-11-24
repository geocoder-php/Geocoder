<?php

namespace Geocoder\Tests\CacheAdapter;

use Geocoder\CacheAdapter\InMemory;
use Geocoder\Tests\TestCase;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class InMemoryTest extends TestCase
{
    protected function setUp()
    {
        $this->cache = new InMemory();
    }

    public function testRetrieve()
    {
        $this->assertNull($this->cache->retrieve('foo'));
        $this->cache->store('foo', 'bar');
        $this->assertEquals('bar', $this->cache->retrieve('foo'));
    }

    /**
     * @dataProvider getCacheData
     */
    public function testStore($key, $value)
    {
        $this->cache->store($key, $value);
        $this->assertEquals($value, $this->cache->retrieve($key));
    }

    static public function getCacheData()
    {
        return array(
            array('foobar', 'bar'),
            array('bar', new \stdClass()),
            array('foo', array('foo', 'bar')),
        );
    }
}
