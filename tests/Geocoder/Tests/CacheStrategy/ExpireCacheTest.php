<?php

namespace Geocoder\Tests\CacheStrategy;

use Geocoder\CacheStrategy\Expire;

class ExpireTest extends \PHPUnit_Framework_TestCase
{
    private $strategy;

    protected function setUp()
    {
        $this->pool = $this->prophesize('Psr\Cache\CacheItemPoolInterface');
        $this->strategy = new Expire($this->pool->reveal(), 100);
    }

    public function testInvokeWithCache()
    {
        $item = $this->prophesize('Psr\Cache\CacheItemInterface');

        $item->isHit()->willReturn(true);
        $item->get()->willReturn('test');
        $item->set()->shouldNotBeCalled();

        $this->pool->getItem('foo')->willReturn($item->reveal());

        $data = $this->strategy->invoke('foo', function() {});

        return $this->assertEquals('test', $data);
    }

    public function testInvokeWithoutCache()
    {
        $item = $this->prophesize('Psr\Cache\CacheItemInterface');

        $item->isHit()->willReturn(false);
        $item->get()->shouldNotBeCalled();
        $item->set('test')->shouldBeCalled();
        $item->expiresAfter(100)->shouldBeCalled();

        $item = $item->reveal();

        $this->pool->getItem('foo')->willReturn($item);
        $this->pool->save($item)->willReturn(true);

        $data = $this->strategy->invoke('foo', function() {
            return 'test';
        });

        return $this->assertEquals('test', $data);
    }
}
