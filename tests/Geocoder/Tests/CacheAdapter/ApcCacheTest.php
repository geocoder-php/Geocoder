<?php

namespace Geocoder\Tests\CacheAdapter;

use Geocoder\CacheAdapter\ApcCache;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class ApcCacheTest extends \PHPUnit_Framework_TestCase {

    protected function setUp()
    {
        if ( ! extension_loaded('apc') ) {
            $this->markTestSkipped('Apc extension must be loaded');
        }
    }

    /**
     * @dataProvider getCacheData
     */
    public function testRetrieveAndStore($key, $value)
    {
        $cache = new ApcCache();
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
