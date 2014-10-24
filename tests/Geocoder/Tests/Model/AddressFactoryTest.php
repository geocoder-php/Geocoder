<?php

namespace Geocoder\Tests\address;

use Geocoder\Model\AddressFactory;
use Geocoder\Tests\TestCase;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 * @author William Durand <william.durand1@gmail.com>
 */
class AddressFactoryTest extends TestCase
{
    private $factory;

    public function setUp()
    {
        $this->factory = new AddressFactory();
    }

    public function testCreateFromArray()
    {

        $addresses = $this->factory->createFromArray([
            [ 'streetNumber' => 1 ],
            [ 'streetNumber' => 2 ],
            [ 'streetNumber' => 3 ],
        ]);

        $this->assertTrue(is_array($addresses));
        $this->assertCount(3, $addresses);

        $i = 1;
        foreach ($addresses as $address) {
            $this->assertInstanceOf('Geocoder\Model\Address', $address);
            $this->assertInstanceOf('Geocoder\Model\Coordinates', $address->getCoordinates());
            $this->assertInstanceOf('Geocoder\Model\County', $address->getCounty());
            $this->assertInstanceOf('Geocoder\Model\Country', $address->getCountry());
            $this->assertInstanceOf('Geocoder\Model\Region', $address->getRegion());

            $this->assertEquals($i++, $address->getStreetNumber());
        }
    }

    public function testFormatStringWithLeadingNumeral()
    {
        if (version_compare(phpversion(), '5.5.16', '<')) {
            $this->markTestSkipped("Character property matching for mb_ereg doesn't work for PHP < 5.5");
        }
        // MB_TITLE_CASE Will turn this into 1St so let's test to ensure we are correcting that
        // We do not want to "correct" 5C, however, as it is part of the original string
        $address = $this->factory->createFromArray([ 'streetName' => '1st ave 1A' ]);

        $this->assertEquals('1st Ave 1A', $address->getStreetName());
    }
}
