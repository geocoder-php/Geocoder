<?php

namespace Geocoder\Tests\Model;

use Geocoder\Model\AddressFactory;
use Geocoder\Location;
use Geocoder\Tests\TestCase;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 * @author William Durand <william.durand1@gmail.com>
 */
class AddressFactoryTest extends TestCase
{
    /** @var AddressFactory */
    private $factory;

    public function setUp()
    {
        $this->factory = new AddressFactory();
    }

    public function testCreateFromArray()
    {

        $addresses = $this->factory->createFromArray([
            [ 'streetNumber' => 1 ],
            [ 'streetNumber' => 2, 'adminLevels' => [['name' => 'admin 1', 'level' => 1]] ],
            [ 'streetNumber' => 3, 'adminLevels' => [['name' => 'admin 2', 'level' => 2], ['name' => 'admin 1', 'level' => 1]] ],
        ]);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $addresses);
        $this->assertCount(3, $addresses);

        $i = 1;
        foreach ($addresses as $location) {
            /** @var $location Location */
            $this->assertInstanceOf('Geocoder\Model\Address', $location);
            $this->assertInstanceOf('Geocoder\Model\Country', $location->getCountry());
            $this->assertNull($location->getCoordinates());

            foreach ($location->getAdminLevels() as $level => $adminLevel) {
                $this->assertInstanceOf('Geocoder\Model\AdminLevel', $adminLevel);
                $this->assertSame($level, $adminLevel->getLevel());
                $this->assertEquals('admin ' . $level, $adminLevel->getName());
            }

            $this->assertEquals($i++, $location->getStreetNumber());
        }
    }

    public function testFormatStringWithLeadingNumeral()
    {
        if (version_compare(phpversion(), '5.5.16', '<')) {
            $this->markTestSkipped("Character property matching for mb_ereg doesn't work for PHP < 5.5");
        }
        // MB_TITLE_CASE Will turn this into 1St so let's test to ensure we are correcting that
        // We do not want to "correct" 5C, however, as it is part of the original string
        $addresses = $this->factory->createFromArray([
            [ 'streetName' => '1st ave 1A' ],
        ]);

        $this->assertEquals('1st ave 1A', $addresses->first()->getStreetName());
    }

    /**
     * @expectedException \Geocoder\Exception\CollectionIsEmpty
     */
    public function testCreateFromEmptyArray()
    {
        $addresses = $this->factory->createFromArray([]);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $addresses);
        $this->assertCount(0, $addresses);

        $addresses->first(); // expecting exception here
    }
}
