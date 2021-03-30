<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Pelias\Tests;

use Geocoder\Provider\Pelias\PeliasAddress;
use PHPUnit\Framework\TestCase;

class PeliasAddressTest extends TestCase
{
    public function testPeliasSpecificDetails(): void
    {
        $address = PeliasAddress::createFromArray([])
            ->withGID('whosonfirst:locality:12345')
            ->withSource('whosonfirst');

        self::assertEquals('whosonfirst:locality:12345', $address->getGID());
        self::assertEquals('whosonfirst', $address->getSource());
    }
}
