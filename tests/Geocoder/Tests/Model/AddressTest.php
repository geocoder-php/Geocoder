<?php

namespace Geocoder\Tests\Model;

use Geocoder\Model\Address;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    public function testDumpEmptyAddress()
    {
        $expected = array(
            'latitude' => NULL,
            'longitude' => NULL,
            'bounds' => array (
                'south' => NULL,
                'west' => NULL,
                'north' => NULL,
                'east' => NULL,
            ),
            'streetNumber' => NULL,
            'streetName' => NULL,
            'postalCode' => NULL,
            'locality' => NULL,
            'subLocality' => NULL,
            'adminLevels' => array(),
            'country' => NULL,
            'countryCode' => NULL,
            'timezone' => NULL,
        );

        $address = new Address();
        $this->assertEquals($address->toArray(), $expected);
    }
}