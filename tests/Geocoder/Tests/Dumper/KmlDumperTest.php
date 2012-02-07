<?php

namespace Geocoder\Tests\Dumper;

use Geocoder\Dumper\KmlDumper;
use Geocoder\Result\Geocoded;
use Geocoder\Tests\TestCase;

/**
 * @author Jan Sorgalla <jsorgalla@googlemail.com>
 */
class KmlDumperTest extends TestCase
{
    private $dumper;

    public function setUp()
    {
        $this->dumper = new KmlDumper();
    }

    public function testDump()
    {
        $obj = new Geocoded();

        $expected = <<<KML
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
    <Document>
        <Placemark>
            <name><![CDATA[]]></name>
            <description><![CDATA[]]></description>
            <Point>
                <coordinates>0.0000000,0.0000000,0</coordinates>
            </Point>
        </Placemark>
    </Document>
</kml>
KML;

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithData()
    {
        $obj = new Geocoded();
        $obj['latitude']  = 48.8631507;
        $obj['longitude'] = 2.3889114;
        $obj->fromArray(array(
            'city' => 'Paris'
        ));

        $expected = <<<KML
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
    <Document>
        <Placemark>
            <name><![CDATA[Paris]]></name>
            <description><![CDATA[Paris]]></description>
            <Point>
                <coordinates>2.3889114,48.8631507,0</coordinates>
            </Point>
        </Placemark>
    </Document>
</kml>
KML;

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }
}
