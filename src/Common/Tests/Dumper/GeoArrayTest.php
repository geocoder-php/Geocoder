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
use Geocoder\Model\Address;
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

    protected function setUp(): void
    {
        $this->dumper = new GeoArray();
    }

    public function testDump(): void
    {
        $address = Address::createFromArray([]);
        $expected = [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [0, 0],
            ],
            'properties' => [
                'providedBy' => 'n/a',
            ],
        ];

        $result = $this->dumper->dump($address);

        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithData(): void
    {
        $address = Address::createFromArray([
            'latitude' => 48.8631507,
            'longitude' => 2.3889114,
        ]);

        $expected = [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [2.3889114, 48.8631507],
            ],
            'properties' => [
                'providedBy' => 'n/a',
            ],
        ];

        $result = $this->dumper->dump($address);

        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithBounds(): void
    {
        $address = Address::createFromArray([
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
            'properties' => [
                'providedBy' => 'n/a',
            ],
            'bounds' => [
                'south' => 48.8631507,
                'west' => 2.3889114,
                'north' => 48.8631507,
                'east' => 2.388911,
            ],
        ];

        $result = $this->dumper->dump($address);

        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithProperties(): void
    {
        $address = Address::createFromArray([
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
                'providedBy' => 'n/a',
            ],
            'bounds' => [
                'south' => 48.8631507,
                'west' => 2.3889114,
                'north' => 48.8631507,
                'east' => 2.388911,
            ],
        ];

        $result = $this->dumper->dump($address);

        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }
}
