<?php

namespace Geocoder\Tests\Model;

use Geocoder\Model\AddressCollection;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class AddressCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Geocoder\Exception\CollectionIsEmpty
     */
    public function testFirstOnEmpty()
    {
        $collection = new AddressCollection([]);
        $collection->first();
    }
}
