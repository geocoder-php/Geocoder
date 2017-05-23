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
use PHPUnit\Framework\TestCase;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class AddressCollectionTest extends TestCase
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
