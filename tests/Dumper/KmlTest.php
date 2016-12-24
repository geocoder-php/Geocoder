<?php

namespace Geocoder\Tests\Dumper;

use Geocoder\Dumper\Kml;
use Geocoder\Tests\TestCase;

/**
 * @author Jan Sorgalla <jsorgalla@googlemail.com>
 * @author William Durand <william.durand1@gmail.com>
 */
class KmlTest extends TestCase
{
    private $dumper;

    public function setUp()
    {
        $this->dumper = new Kml();
    }

    public function testDump()
    {
        $address  = $this->createEmptyAddress();
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

        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithData()
    {
        $address  = $this->createAddress([
            'latitude'  => 48.8631507,
            'longitude' => 2.3889114,
            'locality'      => 'Paris',
        ]);
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

        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }
}
