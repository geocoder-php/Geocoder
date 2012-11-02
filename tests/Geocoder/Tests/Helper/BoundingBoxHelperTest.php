<?php

namespace Geocoder\Tests\Helper;

use Geocoder\Helper\BoundingBoxHelper;
use Geocoder\Result\Geocoded;
use Geocoder\Tests\TestCase;

/**
 * @author Ronan Guilloux <ronan.guilloux@gmail.com>
 */
class BoundingBoxHelperTest extends TestCase
{
    private $helper;

    public function setUp()
    {
        $this->helper = new BoundingBoxHelper();
    }

    public function testGetAngle()
    {
        $obj = new Geocoded();
        $obj->fromArray(array('latitude'=>'47.218371', 'longitude'=>'-1.553621'));

        $expected = new Geocoded();
        // 500 km away from $obj, in the nortEast direction
        $expected->fromArray(array('latitude'=>'50.291525105654', 'longitude'=>'3.4187817544348'));

        $result = $this->helper->getAngle($obj, 45, 500);
        $this->assertTrue($result instanceOf Geocoded);
        $this->assertEquals($expected, $result);
    }

    public function testGetAngleWithZeroDistance()
    {
        $obj = new Geocoded();
        $obj->fromArray(array('latitude'=>'47.218371', 'longitude'=>'-1.553621'));

        $expected = new Geocoded();
        // 0 km away from $obj, in the nortEast direction
        $expected->fromArray(array('latitude'=>'47.218371', 'longitude'=>'-1.553621'));

        $result = $this->helper->getAngle($obj, 45, 0);
        $this->assertTrue($result instanceOf Geocoded);
        $this->assertEquals($expected, $result);
    }
}
