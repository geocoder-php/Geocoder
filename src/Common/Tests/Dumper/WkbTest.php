<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Tests\Dumper;

use Geocoder\Dumper\Wkb;
use Geocoder\Model\Address;
use PHPUnit\Framework\TestCase;

/**
 * @author Jan Sorgalla <jsorgalla@googlemail.com>
 * @author William Durand <william.durand1@gmail.com>
 */
class WkbTest extends TestCase
{
    /**
     * @var Wkb
     */
    private $dumper;

    public function setUp(): void
    {
        $this->dumper = new Wkb();
    }

    public function testDump(): void
    {
        $address = Address::createFromArray([]);
        $expected = pack('H*', '010100000000000000000000000000000000000000');
        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithData(): void
    {
        $address = Address::createFromArray([
            'latitude' => 48.8631507,
            'longitude' => 2.3889114,
        ]);
        $expected = pack('H*', '0101000000255580947D1C03407F02DEB87B6E4840');

        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }
}
