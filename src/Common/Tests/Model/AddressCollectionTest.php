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
    public function testFirstOnEmpty(): void
    {
        $this->expectException(\Geocoder\Exception\CollectionIsEmpty::class);

        $collection = new AddressCollection([]);
        $collection->first();
    }
}
