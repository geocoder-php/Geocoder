<?php

namespace Geocoder\Tests\CacheAdapter;

use Geocoder\CacheAdapter\Filesystem;
use Geocoder\Tests\TestCase;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class FilesystemTest extends TestCase
{

    protected function setUp()
    {
        $this->path = sys_get_temp_dir() . '/geocoder/filesystem_test';
        if (!file_exists($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    protected function tearDown()
    {
        array_map('unlink', glob($this->path.'/*.cache'));
        rmdir($this->path);
        $this->path = null;
    }

    /**
     * @dataProvider getCacheData
     */
    public function testRetrieveAndStore($key, $value)
    {
        $cache = new Filesystem($this->path);
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
