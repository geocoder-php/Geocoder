<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Mapbox\Tests;

use Geocoder\Model\AddressBuilder;
use Geocoder\Provider\Mapbox\Model\MapboxAddress;
use PHPUnit\Framework\TestCase;

class MapboxAddressTest extends TestCase
{
    public function testMatchCode(): void
    {
        $address = (new AddressBuilder('mapbox'))->build(MapboxAddress::class);

        $this->assertNull($address->getMatchCode());

        $matchCode = ['street' => 'matched', 'confidence' => 'exact'];
        $address = $address->withMatchCode($matchCode);

        $this->assertSame($matchCode, $address->getMatchCode());
        $this->assertNull($address->withMatchCode($matchCode)->withMatchCode()->getMatchCode());
    }

    public function testMatchConfidence(): void
    {
        $address = (new AddressBuilder('mapbox'))->build(MapboxAddress::class);

        $this->assertNull($address->getMatchConfidence());

        $address = $address->withMatchConfidence('exact');

        $this->assertSame('exact', $address->getMatchConfidence());
        $this->assertNull($address->withMatchConfidence('exact')->withMatchConfidence()->getMatchConfidence());
    }
}
