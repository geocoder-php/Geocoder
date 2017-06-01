<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Tests\Model;

use Geocoder\Model\Address;
use Geocoder\Model\AdminLevelCollection;
use Geocoder\Model\LocationFactory;
use PHPUnit\Framework\TestCase;

/**
 * @author Antoine Lemaire <lemaireantoine@hotmail.com>
 */
class AddressTest extends TestCase
{
    public function testDumpEmptyAddress()
    {
        $expected = [
            'providedBy' => 'n/a',
            'latitude' => null,
            'longitude' => null,
            'bounds' => [
                'south' => null,
                'west' => null,
                'north' => null,
                'east' => null,
            ],
            'streetNumber' => null,
            'streetName' => null,
            'postalCode' => null,
            'locality' => null,
            'subLocality' => null,
            'adminLevels' => [],
            'country' => null,
            'countryCode' => null,
            'timezone' => null,
        ];

        $address = new Address('n/a', new AdminLevelCollection());
        $this->assertEquals($address->toArray(), $expected);
    }

    public function testToArray()
    {
        $data = [
            'providedBy' => 'n/a',
            'latitude' => 48.8631507,
            'longitude' => 2.3889114,
            'bounds' => [
                'south' => 48.8631507,
                'west' => 2.3889114,
                'north' => 48.8631507,
                'east' => 2.388911,
            ],
            'streetNumber' => '10',
            'streetName' => 'Avenue Gambetta',
            'postalCode' => '75020',
            'locality' => 'Paris',
            'subLocality' => '20e Arrondissement',
            'adminLevels' => [1 => ['name' => 'Ile-de-France', 'code' => 'Ile-de-France', 'level' => 1]],
            'country' => 'France',
            'countryCode' => 'FR',
            'timezone' => null,
        ];

        $address = LocationFactory::createLocation($data);

        $this->assertSame($data, $address->toArray());
    }
}
