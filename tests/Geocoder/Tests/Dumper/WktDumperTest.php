<?php

namespace Geocoder\Tests\Dumper;

use Geocoder\Dumper\WktDumper;
use Geocoder\Result\Geocoded;
use Geocoder\Tests\TestCase;

/**
 * @author Jan Sorgalla <jsorgalla@googlemail.com>
 */
class WktDumperTest extends TestCase
{
    private $dumper;

    public function setUp()
    {
        $this->dumper = new WktDumper();
    }

    public function testDump()
    {
        $obj = new Geocoded();

        $expected = sprintf('POINT(%F %F)', 0, 0);

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithData()
    {
        $obj = new Geocoded();
        $obj['latitude']  = 48.8631507;
        $obj['longitude'] = 2.3889114;

        $expected = sprintf('POINT(%F %F)', 2.3889114, 48.8631507);

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }
}
