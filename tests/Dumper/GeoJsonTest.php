<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Tests\Dumper;

use Geocoder\Dumper\GeoJson;
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
        $address = $this->createAddress([]);
        $expected = [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [0, 0],
            ],
            'properties' => null,
        ];

        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, json_decode($result, true));
    }

    public function testDumpWithData()
    {
        $address = $this->createAddress([
            'latitude' => 48.8631507,
            'longitude' => 2.3889114,
        ]);

        $expected = [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [2.3889114, 48.8631507],
            ],
            'properties' => null,
        ];

        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, json_decode($result, true));
    }

    public function testDumpWithBounds()
    {
        $address = $this->createAddress([
            'latitude' => 48.8631507,
            'longitude' => 2.3889114,
            'bounds' => [
                'south' => 48.8631507,
                'west' => 2.3889114,
                'north' => 48.8631507,
                'east' => 2.388911,
            ],
        ]);

        $expected = [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [2.3889114, 48.8631507],
            ],
            'properties' => null,
            'bounds' => [
                'south' => 48.8631507,
                'west' => 2.3889114,
                'north' => 48.8631507,
                'east' => 2.388911,
            ],
        ];

        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, json_decode($result, true));
    }

    public function testDumpWithProperties()
    {
        $address = $this->createAddress([
            'latitude' => 48.8631507,
            'longitude' => 2.3889114,
            'bounds' => [
                'south' => 48.8631507,
                'west' => 2.3889114,
                'north' => 48.8631507,
                'east' => 2.388911,
            ],
            'locality' => 'Paris',
            'country' => 'France',
        ]);

        $expected = [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [2.3889114, 48.8631507],
            ],
            'properties' => [
                'locality' => 'Paris',
                'country' => 'France',
            ],
            'bounds' => [
                'south' => 48.8631507,
                'west' => 2.3889114,
                'north' => 48.8631507,
                'east' => 2.388911,
            ],
        ];

        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, json_decode($result, true));
    }
}
