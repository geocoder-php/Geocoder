<?php

namespace Geocoder\Tests\Provider;

use Prophecy\Argument;
use Geocoder\Provider\Cache;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->strategy = $this->prophesize('Geocoder\CacheStrategy\Strategy');
        $this->delegate = $this->prophesize('Geocoder\Provider\Provider');

        $this->provider = new Cache($this->strategy->reveal(), $this->delegate->reveal());
    }


    public function testGeocode()
    {
        $this->strategy->invoke(
            'geocoder_3bd614cd786c546800755606b56dfce3863cffa6',
            Argument::type('Closure')
        )->willReturn('foo');

        $this->assertEquals('foo', $this->provider->geocode('Alexander Platz 1, Berlin'));
    }

    public function testReverse()
    {
        $this->strategy->invoke(
            'geocoder_20889549b60b05ddbac1f40d34259d8dee5f9835',
            Argument::type('Closure')
        )->willReturn('foo');

        $this->assertEquals('foo', $this->provider->reverse(55.56688, 78.51426));
    }

    public function testGetName()
    {
        $this->assertEquals('cache', $this->provider->getName());
    }

    public function testLimit()
    {
        $this->delegate->limit(20)->shouldBeCalled();

        $this->provider->limit(20);
    }

    public function testGetLimit()
    {
        $this->delegate->getLimit()->willReturn(20);

        $this->assertEquals(20, $this->provider->getLimit());
    }
}
