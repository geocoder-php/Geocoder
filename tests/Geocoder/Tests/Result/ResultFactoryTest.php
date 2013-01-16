<?php

namespace Geocoder\Tests\Result;

use Geocoder\Result\ResultFactory;
use Geocoder\Tests\TestCase;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class ResultFactoryTest extends TestCase
{
    public function testNewInstance()
    {
        $factory = new ResultFactory();
        $result  = $factory->newInstance();

        $this->assertTrue(is_object($result));
        $this->assertInstanceOf('Geocoder\Result\Geocoded', $result);
        $this->assertInstanceOf('Geocoder\Result\ResultInterface', $result);
    }

    public function testCreateFromArray()
    {
        $factory = new ResultFactory();
        $result  = $factory->createFromArray(array(
            'latitude'  => 123,
            'longitude' => 456,
        ));

        $this->assertTrue(is_object($result));
        $this->assertInstanceOf('Geocoder\Result\Geocoded', $result);
        $this->assertInstanceOf('Geocoder\Result\ResultInterface', $result);
        $this->assertEquals(123, $result->getLatitude());
        $this->assertEquals(456, $result->getLongitude());
    }
}
