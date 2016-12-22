<?php

namespace Geocoder\Tests\Dumper;

use Geocoder\Dumper\Wkb;
use Geocoder\Tests\TestCase;

/**
 * @author Jan Sorgalla <jsorgalla@googlemail.com>
 * @author William Durand <william.durand1@gmail.com>
 */
class WkbTest extends TestCase
{
    private $dumper;

    public function setUp()
    {
        $this->dumper = new Wkb();
    }

    public function testDump()
    {
        $address  = $this->createEmptyAddress();
        $expected = pack('H*', '010100000000000000000000000000000000000000');
        $result   = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithData()
    {
        $address  = $this->createAddress([
            'latitude'  => 48.8631507,
            'longitude' => 2.3889114,
        ]);
        $expected = pack('H*', '0101000000255580947D1C03407F02DEB87B6E4840');

        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }
}
