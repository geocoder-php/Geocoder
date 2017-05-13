<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Tests\Model;

use Geocoder\Model\Address;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    public function testDumpEmptyAddress()
    {
        $expected = [
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

        $address = new Address();
        $this->assertEquals($address->toArray(), $expected);
    }
}
