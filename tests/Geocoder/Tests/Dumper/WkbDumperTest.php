<?php

namespace Geocoder\Tests\Dumper;

use Geocoder\Dumper\WkbDumper;
use Geocoder\Result\Geocoded;
use Geocoder\Tests\TestCase;

/**
 * @author Jan Sorgalla <jsorgalla@googlemail.com>
 */
class WkbDumperTest extends TestCase
{
    private $dumper;

    public function setUp()
    {
        $this->dumper = new WkbDumper();
    }

    public function testDump()
    {
        $obj = new Geocoded();

        $expected = pack('H*', '010100000000000000000000000000000000000000');

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithData()
    {
        $obj = new Geocoded();
        $obj['latitude']  = 48.8631507;
        $obj['longitude'] = 2.3889114;

        $expected = pack('H*', '0101000000255580947D1C03407F02DEB87B6E4840');

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }
}
