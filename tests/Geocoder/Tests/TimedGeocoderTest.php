<?php

namespace Geocoder\Tests;

use Geocoder\TimedGeocoder;
use Symfony\Component\Stopwatch\Stopwatch;

class TimedGeocoderTest extends TestCase
{
    protected function setUp()
    {
        $this->stopwatch = new Stopwatch();
        $this->delegate = $this->getMock('Geocoder\Geocoder');
        $this->geocoder = new TimedGeocoder($this->delegate, $this->stopwatch);
    }

    public function testGeocode()
    {
        $this->delegate->expects($this->once())
             ->method('geocode')
             ->with($this->equalTo('foo'))
             ->will($this->returnValue([]));

        $this->geocoder->geocode('foo');

        $this->assertCount(1, $this->stopwatch->getSectionEvents('__root__'));
    }

    public function testGeocodeThrowsException()
    {
        $this->delegate->expects($this->once())
             ->method('geocode')
             ->with($this->equalTo('foo'))
             ->will($this->throwException($exception = new \Exception()));

        try {
            $this->geocoder->geocode('foo');
            $this->fail('Geocoder::geocode should throw an exception');
        } catch (\Exception $e) {
            $this->assertSame($exception, $e);
        }

        $this->assertCount(1, $this->stopwatch->getSectionEvents('__root__'));
    }

    public function testReverse()
    {
        $this->delegate->expects($this->once())
             ->method('reverse')
             ->with($this->equalTo(0, 0))
             ->will($this->returnValue([]));

        $this->geocoder->reverse(0, 0);

        $this->assertCount(1, $this->stopwatch->getSectionEvents('__root__'));
    }

    public function testReverseThrowsException()
    {
        $this->delegate->expects($this->once())
             ->method('reverse')
             ->with($this->equalTo(0, 0))
             ->will($this->throwException($exception = new \Exception()));

        try {
            $this->geocoder->reverse(0, 0);
            $this->fail('Geocoder::reverse should throw an exception');
        } catch (\Exception $e) {
            $this->assertSame($exception, $e);
        }

        $this->assertCount(1, $this->stopwatch->getSectionEvents('__root__'));
    }
}
