<?php

namespace Geocoder\Tests\Result;

use Geocoder\Result\Geocoded;
use Geocoder\Tests\TestCase;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class GeocodedTest extends TestCase
{
    protected $geocoded;

    protected function setUp()
    {
        $this->geocoded = new Geocoded();
    }

    public function testFromData()
    {
        $array = array(
            'latitude'  => 0.001,
            'longitude' => 1,
            'city'      => 'FOo CITY',
            'zipcode'   => '65943',
            'region'    => 'FOO region',
            'country'   => 'FOO Country'
        );

        $this->geocoded->fromArray($array);

        $this->assertEquals(0.001, $this->geocoded->getLatitude());
        $this->assertEquals(1, $this->geocoded->getLongitude());
        $this->assertEquals('Foo City', $this->geocoded->getCity());
        $this->assertEquals('65943', $this->geocoded->getZipcode());
        $this->assertEquals('Foo Region', $this->geocoded->getRegion());
        $this->assertEquals('Foo Country', $this->geocoded->getCountry());
    }

    public function testFromDataWithEmptyArray()
    {
        $this->geocoded->fromArray(array());

        $this->assertEquals(0, $this->geocoded->getLatitude());
        $this->assertEquals(0, $this->geocoded->getLongitude());
        $this->assertEquals('', $this->geocoded->getCity());
        $this->assertEquals('', $this->geocoded->getZipcode());
        $this->assertEquals('', $this->geocoded->getRegion());
        $this->assertEquals('', $this->geocoded->getCountry());
    }

    public function testFromDataWithNull()
    {
        $array = array(
            'latitude'  => 100,
            'longitude' => 1.2
        );

        $this->geocoded->fromArray($array);

        $this->assertEquals(100, $this->geocoded->getLatitude());
        $this->assertEquals(1.2, $this->geocoded->getLongitude());
        $this->assertEquals('', $this->geocoded->getCity());
        $this->assertEquals('', $this->geocoded->getZipcode());
        $this->assertEquals('', $this->geocoded->getRegion());
        $this->assertEquals('', $this->geocoded->getCountry());
    }
}
