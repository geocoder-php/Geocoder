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

use Geocoder\Model\AddressCollection;
use Geocoder\Model\LocationFactory;
use Geocoder\Location;
use PHPUnit\Framework\TestCase;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 * @author William Durand <william.durand1@gmail.com>
 */
class LocationFactoryTest extends TestCase
{
    public function testCreateFromArray()
    {
        $addresses = new AddressCollection([
            LocationFactory::createLocation(['streetNumber' => '1']),
            LocationFactory::createLocation(['streetNumber' => '2', 'adminLevels' => [['name' => 'admin 1', 'level' => 1]]]),
            LocationFactory::createLocation(['streetNumber' => '3', 'adminLevels' => [['name' => 'admin 2', 'level' => 2], ['name' => 'admin 1', 'level' => 1]]]),
        ]);

        $this->assertInstanceOf('Geocoder\Model\AddressCollection', $addresses);
        $this->assertCount(3, $addresses);

        $i = 1;
        foreach ($addresses as $location) {
            /* @var $location Location */
            $this->assertInstanceOf('Geocoder\Model\Address', $location);
            $this->assertInstanceOf('Geocoder\Model\Country', $location->getCountry());
            $this->assertNull($location->getCoordinates());

            foreach ($location->getAdminLevels() as $level => $adminLevel) {
                $this->assertInstanceOf('Geocoder\Model\AdminLevel', $adminLevel);
                $this->assertSame($level, $adminLevel->getLevel());
                $this->assertEquals('admin '.$level, $adminLevel->getName());
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
        $address = LocationFactory::createLocation(['streetName' => '1st ave 1A']);

        $this->assertEquals('1st ave 1A', $address->getStreetName());
    }

    public function testCreateFromEmptyArray()
    {
        $location = LocationFactory::createLocation([]);
        $this->assertInstanceOf(Location::class, $location);
    }
}
