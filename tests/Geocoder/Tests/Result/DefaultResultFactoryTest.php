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

    /**
     * @dataProvider arrayProvider
     */
    public function testCreateFromArray($array, $expected)
    {
        $factory = new DefaultResultFactory();
        $result  = $factory->createFromArray($array);

        $this->assertInstanceOf('Geocoder\Result\Geocoded', $result);
        $this->assertInstanceOf('Geocoder\Result\ResultInterface', $result);
        $this->assertEquals($expected['latitude'], $result->getLatitude());
        $this->assertEquals($expected['longitude'], $result->getLongitude());
    }

    public function arrayProvider()
    {
        return array(
            array(
                array(
                    array('latitude' => 123, 'longitude' => 456)
                ),
                array('latitude' => 123, 'longitude' => 456),
            ),
            array(
                array('latitude' => 123, 'longitude' => 456),
                array('latitude' => 123, 'longitude' => 456),
            ),
        );
    }
}
