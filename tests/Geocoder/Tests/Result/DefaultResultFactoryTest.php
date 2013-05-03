<?php

namespace Geocoder\Tests\Result;

use Geocoder\Result\DefaultResultFactory;
use Geocoder\Tests\TestCase;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class DefaultResultFactoryTest extends TestCase
{
    public function testNewInstance()
    {
        $factory = new DefaultResultFactory();
        $result  = $factory->newInstance();

        $this->assertInstanceOf('Geocoder\Result\Geocoded', $result);
        $this->assertInstanceOf('Geocoder\Result\ResultInterface', $result);
    }

    public function testCreateFromArray()
    {
        $factory = new DefaultResultFactory();
        $result  = $factory->createFromArray(array(
            'latitude'  => 123,
            'longitude' => 456,
        ));

        $this->assertInstanceOf('Geocoder\Result\Geocoded', $result);
        $this->assertInstanceOf('Geocoder\Result\ResultInterface', $result);
        $this->assertEquals(123, $result->getLatitude());
        $this->assertEquals(456, $result->getLongitude());
    }
}
