<?php

namespace Geocoder\Tests\Dumper;

use Geocoder\Dumper\GeoJsonDumper;
use Geocoder\Result\Geocoded;
use Geocoder\Tests\TestCase;

/**
 * @author Jan Sorgalla <jsorgalla@googlemail.com>
 */
class GeoJsonDumperTest extends TestCase
{
    private $dumper;

    public function setUp()
    {
        $this->dumper = new GeoJsonDumper();
    }

    public function testDump()
    {
        $obj = new Geocoded();

        $expected = array(
            'type' => 'Feature',
            'geometry' => array(
                'type' => 'Point',
                'coordinates' => array(0, 0)
            ),
            'properties' => null
        );

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, json_decode($result, true));
    }

    public function testDumpWithData()
    {
        $obj = new Geocoded();
        $obj['latitude']  = 48.8631507;
        $obj['longitude'] = 2.3889114;

        $expected = array(
            'type' => 'Feature',
            'geometry' => array(
                'type' => 'Point',
                'coordinates' => array(2.3889114, 48.8631507)
            ),
            'properties' => null
        );

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, json_decode($result, true));
    }

    public function testDumpWithBounds()
    {
        $obj = new Geocoded();
        $obj['latitude']  = 48.8631507;
        $obj['longitude'] = 2.3889114;
        $obj->fromArray(array(
            'bounds' => array(
                'south' => 48.8631507,
                'west'  => 2.3889114,
                'north' => 48.8631507,
                'east'  => 2.388911
            )
        ));

        $expected = array(
            'type' => 'Feature',
            'geometry' => array(
                'type' => 'Point',
                'coordinates' => array(2.3889114, 48.8631507)
            ),
            'properties' => null,
            'bounds' => array(
                'south' => 48.8631507,
                'west'  => 2.3889114,
                'north' => 48.8631507,
                'east'  => 2.388911
            )
        );

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, json_decode($result, true));
    }

    public function testDumpWithProperties()
    {
        $obj = new Geocoded();
        $obj['latitude']  = 48.8631507;
        $obj['longitude'] = 2.3889114;
        $obj->fromArray(array(
            'bounds' => array(
                'south' => 48.8631507,
                'west'  => 2.3889114,
                'north' => 48.8631507,
                'east'  => 2.388911
            ),
            'city' => 'Paris',
            'country' => 'France'
        ));

        $expected = array(
            'type' => 'Feature',
            'geometry' => array(
                'type' => 'Point',
                'coordinates' => array(2.3889114, 48.8631507)
            ),
            'properties' => array(
                'city' => 'Paris',
                'country' => 'France'
            ),
            'bounds' => array(
                'south' => 48.8631507,
                'west'  => 2.3889114,
                'north' => 48.8631507,
                'east'  => 2.388911
            )
        );

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, json_decode($result, true));
    }
}
