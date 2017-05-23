<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Tests\Dumper;

use Geocoder\Dumper\GeoArray;
use Geocoder\Model\LocationFactory;
use PHPUnit\Framework\TestCase;

/**
 * @author Tomas Norkūnas <norkunas.tom@gmail.com>
 */
class GeoArrayTest extends TestCase
{
    /**
     * @var GeoArray
     */
    private $dumper;

    protected function setUp()
    {
        $this->dumper = new GeoArray();
    }

    public function testDump()
    {
        $address = LocationFactory::createLocation([]);
        $expected = [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [0, 0],
            ],
            'properties' => null,
        ];

        $result = $this->dumper->dump($address);

        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithData()
    {
        $address = LocationFactory::createLocation([
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

        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithBounds()
    {
        $address = LocationFactory::createLocation([
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

        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithProperties()
    {
        $address = LocationFactory::createLocation([
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

        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }
}
