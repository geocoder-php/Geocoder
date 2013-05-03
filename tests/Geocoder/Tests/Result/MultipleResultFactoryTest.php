<?php

namespace Geocoder\Tests\Result;

use Geocoder\Result\MultipleResultFactory;
use Geocoder\Tests\TestCase;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class MultipleResultFactoryTest extends TestCase
{
    public function testNewInstance()
    {
        $factory = new MultipleResultFactory();
        $result  = $factory->newInstance();

        $this->assertInstanceOf('Geocoder\Result\Geocoded', $result);
        $this->assertInstanceOf('Geocoder\Result\ResultInterface', $result);
    }

    public function testCreateFromArray()
    {
        $factory = new MultipleResultFactory();
        $results = $factory->createFromArray(array(
            array(),
            array(),
            array(),
        ));

        $this->assertInstanceOf('\SplObjectStorage', $results);
        $this->assertCount(3, $results);
        foreach ($results as $result) {
            $this->assertInstanceOf('Geocoder\Result\Geocoded', $result);
            $this->assertInstanceOf('Geocoder\Result\ResultInterface', $result);
        }
    }
}
