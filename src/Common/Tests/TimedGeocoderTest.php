<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Tests;

use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;
use Geocoder\TimedGeocoder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Stopwatch\Stopwatch;

class TimedGeocoderTest extends TestCase
{
    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @var Provider&MockObject
     */
    private $delegate;

    /**
     * @var TimedGeocoder
     */
    private $geocoder;

    protected function setUp(): void
    {
        $this->stopwatch = new Stopwatch();
        $this->delegate = $this->getMockBuilder(Provider::class)->getMock();
        $this->geocoder = new TimedGeocoder($this->delegate, $this->stopwatch);
    }

    public function testGeocode(): void
    {
        $this->delegate->expects($this->once())
             ->method('geocodeQuery')
             ->will($this->returnValue(new AddressCollection([])));

        $this->geocoder->geocode('foo');

        $this->assertCount(1, $this->stopwatch->getSectionEvents('__root__'));
    }

    public function testGeocodeThrowsException(): void
    {
        $this->delegate->expects($this->once())
             ->method('geocodeQuery')
             ->will($this->throwException($exception = new \Exception()));

        try {
            $this->geocoder->geocode('foo');
            $this->fail('Geocoder::geocode should throw an exception');
        } catch (\Exception $e) {
            $this->assertSame($exception, $e);
        }

        $this->assertCount(1, $this->stopwatch->getSectionEvents('__root__'));
    }

    public function testReverse(): void
    {
        $this->delegate->expects($this->once())
             ->method('reverseQuery')
             ->will($this->returnValue(new AddressCollection([])));

        $this->geocoder->reverse(0, 0);

        $this->assertCount(1, $this->stopwatch->getSectionEvents('__root__'));
    }

    public function testReverseThrowsException(): void
    {
        $this->delegate->expects($this->once())
             ->method('reverseQuery')
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
