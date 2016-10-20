<?php

namespace Geocoder\Tests\Model;

use Geocoder\Model\AddressFactory;
use Geocoder\Tests\TestCase;

/**
 * @author Antoine Lemaire <lemaireantoine@hotmail.com>
 */
class AddressTest extends TestCase
{
    public function testToArray()
    {
        $data = [
            'latitude'  => 48.8631507,
            'longitude' => 2.3889114,
            'bounds'    => [
                'south' => 48.8631507,
                'west'  => 2.3889114,
                'north' => 48.8631507,
                'east'  => 2.388911,
            ],
            'streetNumber' => '10',
            'streetName'   => 'Avenue Gambetta',
            'postalCode'   => '75020',
            'locality'     => 'Paris',
            'subLocality'  => '20e Arrondissement',
            'adminLevels'  => [1 => ['name' => 'Ile-de-France', 'code' => 'Ile-de-France', 'level' => 1]],
            'country'      => 'France',
            'countryCode'  => 'FR',
            'timezone'     => null,
        ];

        $address = $this->createAddress($data);

        $this->assertSame($data, $address->toArray());
    }

    public function testCreateFromArrayFromToArray()
    {
        $factory = new AddressFactory();

        $data = [
            'latitude'  => 48.8631507,
            'longitude' => 2.3889114,
            'bounds'    => [
                'south' => 48.8631507,
                'west'  => 2.3889114,
                'north' => 48.8631507,
                'east'  => 2.388911,
            ],
            'streetNumber' => '10',
            'streetName'   => 'Avenue Gambetta',
            'postalCode'   => '75020',
            'locality'     => 'Paris',
            'subLocality'  => '20e Arrondissement',
            'adminLevels'  => [1 => ['name' => 'Ile-de-France', 'code' => 'Ile-de-France', 'level' => 1]],
            'country'      => 'France',
            'countryCode'  => 'FR',
            'timezone'     => null,
        ];

        $address   = $this->createAddress($data);
        $array     = $address->toArray();
        $addresses = $factory->createFromArray([$array]);

        $this->assertEquals($address, $addresses->get(0));
    }
}
