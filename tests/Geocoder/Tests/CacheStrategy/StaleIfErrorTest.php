<?php

namespace Geocoder\Tests\CacheStrategy;

use Geocoder\CacheStrategy\StaleIfError;

class StaleIfErrorTest extends \PHPUnit_Framework_TestCase
{
    private $strategy;

    protected function setUp()
    {
        $this->pool = $this->prophesize('Psr\Cache\CacheItemPoolInterface');
        $this->strategy = new StaleIfError($this->pool->reveal(), 100);
    }

    public function testValidResponse()
    {
        $item = $this->prophesize('Psr\Cache\CacheItemInterface');

        $item->expiresAfter(100)->shouldBeCalled();
        $item->set('test')->shouldBeCalled();

        $item->get()->shouldNotBeCalled();

        $item = $item->reveal();

        $this->pool->getItem('foo')->willReturn($item);
        $this->pool->save($item)->willReturn(true);

        $data = $this->strategy->invoke('foo', function() {
            return 'test';
        });

        $this->assertEquals('test', $data);
    }

    public function testCatchExceptionAndReturnCache()
    {
        $item = $this->prophesize('Psr\Cache\CacheItemInterface');
        $item->isHit()->willReturn(true);
        $item->get()->willReturn('test');

        $item = $item->reveal();

        $this->pool->getItem('foo')->willReturn($item);
        $this->pool->save()->shouldNotBeCalled();

        $data = $this->strategy->invoke('foo', function() {
            throw new \Exception();
        });

        $this->assertEquals('test', $data);
    }

    /**
     * @expectedException \Exception
     */
    public function testCatchExceptionAndHaveNoCache()
    {
        $item = $this->prophesize('Psr\Cache\CacheItemInterface');
        $item->isHit()->willReturn(false);
        $item->get()->shouldNotBeCalled();

        $this->pool->getItem('foo')->willReturn($item->reveal());

        $this->strategy->invoke('foo', function() {
            throw new \Exception();
        });
    }
}
