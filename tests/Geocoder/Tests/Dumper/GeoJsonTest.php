<?php

namespace Geocoder\Tests\Dumper;

use Geocoder\Dumper\GeoJson;
use Geocoder\Model\Address;
use Geocoder\Tests\TestCase;

/**
 * @author Jan Sorgalla <jsorgalla@googlemail.com>
 * @author William Durand <william.durand1@gmail.com>
 */
class GeoJsonTest extends TestCase
{
    private $dumper;

    public function setUp()
    {
        $this->dumper = new GeoJson();
    }

    public function testDump()
    {
        $address  = $this->createAddress([]);
        $expected = array(
            'type' => 'Feature',
            'geometry' => array(
                'type' => 'Point',
                'coordinates' => array(0, 0)
            ),
            'properties' => null
        );

        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, json_decode($result, true));
    }

    public function testDumpWithData()
    {
        $address = $this->createAddress([
            'latitude'  => 48.8631507,
            'longitude' => 2.3889114
        ]);

        $expected = array(
            'type' => 'Feature',
            'geometry' => array(
                'type' => 'Point',
                'coordinates' => array(2.3889114, 48.8631507)
            ),
            'properties' => null
        );

        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, json_decode($result, true));
    }

    public function testDumpWithBounds()
    {
        $address = $this->createAddress([
            'latitude'  => 48.8631507,
            'longitude' => 2.3889114,
            'bounds' => [
                'south' => 48.8631507,
                'west'  => 2.3889114,
                'north' => 48.8631507,
                'east'  => 2.388911
            ]
        ]);

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

        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, json_decode($result, true));
    }

    public function testDumpWithProperties()
    {
        $address = $this->createAddress([
            'latitude'  => 48.8631507,
            'longitude' => 2.3889114,
            'bounds' => [
                'south' => 48.8631507,
                'west'  => 2.3889114,
                'north' => 48.8631507,
                'east'  => 2.388911
            ],
            'locality'  => 'Paris',
            'country' => 'France'
        ]);

        $expected = array(
            'type' => 'Feature',
            'geometry' => array(
                'type' => 'Point',
                'coordinates' => array(2.3889114, 48.8631507)
            ),
            'properties' => array(
                'locality' => 'Paris',
                'country' => 'France'
            ),
            'bounds' => array(
                'south' => 48.8631507,
                'west'  => 2.3889114,
                'north' => 48.8631507,
                'east'  => 2.388911
            )
        );

        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, json_decode($result, true));
    }
}
