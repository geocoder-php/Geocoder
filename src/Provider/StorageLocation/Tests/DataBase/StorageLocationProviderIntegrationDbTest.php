<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\StorageLocation\Tests\DataBase;

use Geocoder\Model\Address;
use Geocoder\Provider\StorageLocation\DataBase\DataBaseInterface;
use Geocoder\Provider\StorageLocation\Model\Place;
use PHPUnit\Framework\TestCase;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
abstract class StorageLocationProviderIntegrationDbTest extends TestCase
{
    /** @var DataBaseInterface */
    protected $dataBase;

    public function testAdd()
    {
        $origPlace = Place::createFromArray(json_decode(
            file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'places'.DIRECTORY_SEPARATOR.'add.place'),
            true
        ));

        $this->dataBase->add($origPlace);

        $places = array_values($this->dataBase->get($this->dataBase->compileKey($origPlace->getSelectedAddress())));

        $this->assertEquals([$origPlace], $places);
    }

    public function testUpdate()
    {
        $origPlace = json_decode(
            file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'places'.DIRECTORY_SEPARATOR.'update.place'),
            true
        );
        $placeObj = Place::createFromArray($origPlace);
        $this->dataBase->add($placeObj);

        $this->assertEquals(
            [$placeObj],
            array_values($this->dataBase->get($this->dataBase->compileKey($placeObj->getSelectedAddress())))
        );

        $placeObj->setSelectedAddress(Address::createFromArray(array_merge(
            $origPlace['address']['en'],
            ['timezone' => 'Control time zone']
        )));
        $this->dataBase->update($placeObj);
        $this->assertEquals(
            [$placeObj],
            array_values($this->dataBase->get($this->dataBase->compileKey($placeObj->getSelectedAddress())))
        );
    }

    public function testDelete()
    {
        $origPlace = Place::createFromArray(json_decode(
            file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'places'.DIRECTORY_SEPARATOR.'delete.place'),
            true
        ));
        $this->dataBase->add($origPlace);
        $this->assertEquals(
            [$origPlace],
            array_values($this->dataBase->get($this->dataBase->compileKey($origPlace->getSelectedAddress())))
        );

        $this->dataBase->delete($origPlace);
        $this->assertEquals([], $this->dataBase->get($this->dataBase->compileKey($origPlace->getSelectedAddress())));
    }

    public function testGetAllPlaces()
    {
        $origPlace = Place::createFromArray(json_decode(
            file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'places'.DIRECTORY_SEPARATOR.'add.place'),
            true
        ));
        $this->dataBase->add($origPlace);

        $result = $this->dataBase->getAllPlaces();

        $this->assertTrue(is_array($result));
        $this->assertGreaterThan(0, count($result));
        foreach ($result as $place) {
            $this->assertEquals(Place::class, get_class($place));
        }
    }

    public function testGetAdminLevels()
    {
        $origPlace = Place::createFromArray(json_decode(
            file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'places'.DIRECTORY_SEPARATOR.'add.place'),
            true
        ));
        $this->dataBase->add($origPlace);

        $result = $this->dataBase->getAdminLevels();

        $this->assertTrue(is_array($result));
        $this->assertGreaterThan(0, count($result));
        foreach ($result as $level) {
            $this->assertTrue(is_int($level));
        }
    }

    /**
     * @after
     */
    public function cleanUp()
    {
        $page = 0;
        $limit = 30;

        while ($result = $this->dataBase->getAllPlaces($page * $limit, $limit)) {
            foreach ($result as $place) {
                $this->dataBase->delete($place);
            }
            ++$page;

            if (count($result) < $limit) {
                break;
            }
        }
    }
}
