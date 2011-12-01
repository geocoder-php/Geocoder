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
            'bounds'    => array(
                'south' => 1,
                'west'  => '2',
                'north' => 3,
                'east'  => 0.1
            ),
            'city'      => 'FOo CITY',
            'zipcode'   => '65943',
            'region'    => 'FOO region',
            'country'   => 'FOO Country'
        );

        $this->geocoded->fromArray($array);
        
        $bounds = $this->geocoded->getBounds();

        $this->assertEquals(0.001, $this->geocoded->getLatitude());
        $this->assertEquals(1, $this->geocoded->getLongitude());
        $this->assertArrayHasKey('south', $bounds);
        $this->assertArrayHasKey('west', $bounds);
        $this->assertArrayHasKey('north', $bounds);
        $this->assertArrayHasKey('east', $bounds);
        $this->assertEquals(1, $bounds['south']);
        $this->assertEquals(2, $bounds['west']);
        $this->assertEquals(3, $bounds['north']);
        $this->assertEquals(0.1, $bounds['east']);
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
        $this->assertNull($this->geocoded->getBounds());
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
        $this->assertNull($this->geocoded->getBounds());
        $this->assertEquals('', $this->geocoded->getCity());
        $this->assertEquals('', $this->geocoded->getZipcode());
        $this->assertEquals('', $this->geocoded->getRegion());
        $this->assertEquals('', $this->geocoded->getCountry());
    }

    public function testArrayInterface()
    {
        $array = array(
            'latitude'  => 0.001,
            'longitude' => 1,
            'bounds'    => array(
                'south' => 1,
                'west'  => '2',
                'north' => 3,
                'east'  => 0.1
            ),
            'city'      => 'FOo CITY',
            'zipcode'   => '65943',
            'region'    => 'FOO region',
            'country'   => 'FOO Country'
        );

        $this->geocoded->fromArray($array);

        // array access
        $this->assertEquals(0.001, $this->geocoded['latitude']);
        $this->assertEquals(1, $this->geocoded['longitude']);
        $this->assertInternalType('array', $this->geocoded['bounds']);
        $this->assertEquals('Foo City', $this->geocoded['city']);
        $this->assertEquals('65943', $this->geocoded['zipcode']);
        $this->assertEquals('Foo Region', $this->geocoded['region']);
        $this->assertEquals('Foo Country', $this->geocoded['country']);

        // array access is case independant
        $this->assertEquals(0.001, $this->geocoded['LATITUDE']);
        $this->assertEquals(1, $this->geocoded['LONGITUDE']);
        $this->assertInternalType('array', $this->geocoded['BOUNDS']);
        $this->assertEquals('Foo City', $this->geocoded['CITY']);
        $this->assertEquals('65943', $this->geocoded['ZIPCODE']);
        $this->assertEquals('Foo Region', $this->geocoded['REGION']);
        $this->assertEquals('Foo Country', $this->geocoded['COUNTRY']);

        // isset
        $this->assertEquals(true, isset($this->geocoded['latitude']));
        $this->assertEquals(true, isset($this->geocoded['longitude']));
        $this->assertEquals(true, isset($this->geocoded['bounds']));
        $this->assertEquals(true, isset($this->geocoded['city']));
        $this->assertEquals(true, isset($this->geocoded['zipcode']));
        $this->assertEquals(true, isset($this->geocoded['region']));
        $this->assertEquals(true, isset($this->geocoded['country']));
        $this->assertEquals(false, isset($this->geocoded['other']));

        // set
        $this->geocoded['latitude'] = 0.123456;
        $this->assertEquals(0.123456, $this->geocoded['latitude']);
    }
}
