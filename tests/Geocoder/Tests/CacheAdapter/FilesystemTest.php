<?php

namespace Geocoder\Tests\CacheAdapter;

use Geocoder\CacheAdapter\Filesystem;
use Geocoder\Tests\TestCase;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class FilesystemTest extends TestCase
{
    /**
     * @dataProvider getCacheData
     */
    public function testRetrieveAndStore($key, $value)
    {
        $cache = new Filesystem(sys_get_temp_dir());
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
