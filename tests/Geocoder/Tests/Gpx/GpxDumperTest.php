<?php

namespace Geocoder\Tests\Gpx;

use Geocoder\Geocoder;
use Geocoder\Gpx\GpxDumper;
use Geocoder\Result\Geocoded;
use Geocoder\Tests\TestCase;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class GpxDumperTest extends TestCase
{
    private $dumper;

    public function setUp()
    {
        $this->dumper = new GpxDumper();
    }

    public function testDump()
    {
        $obj = new Geocoded();

        $expected = sprintf(<<<GPX
<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
<gpx
version="1.0"
    creator="Geocoder" version="%s"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/0"
    xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd">
    <wpt lat="0.0000000" lon="0.0000000">
        <type><![CDATA[Address]]></type>
    </wpt>
</gpx>
GPX
        , Geocoder::VERSION);

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithData()
    {
        $obj = new Geocoded();
        $obj['latitude']  = 48.8631507;
        $obj['longitude'] = 2.3889114;

        $expected = sprintf(<<<GPX
<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
<gpx
version="1.0"
    creator="Geocoder" version="%s"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/0"
    xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd">
    <wpt lat="48.8631507" lon="2.3889114">
        <type><![CDATA[Address]]></type>
    </wpt>
</gpx>
GPX
        , Geocoder::VERSION);

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithBounds()
    {
        $obj = new Geocoded();
        $obj['latitude']  = 48.8631507;
        $obj['longitude'] = 2.3889114;
        $obj->fromArray(array(
            'bounds' => array(
                'south' => 48.8631507,
                'west'  => 2.3889114,
                'north' => 48.8631507,
                'east'  => 2.388911
            )
        ));

        $expected = sprintf(<<<GPX
<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
<gpx
version="1.0"
    creator="Geocoder" version="%s"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/0"
    xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd">
    <bounds minlat="2.388911" minlon="48.863151" maxlat="2.388911" maxlon="48.863151"/>
    <wpt lat="48.8631507" lon="2.3889114">
        <type><![CDATA[Address]]></type>
    </wpt>
</gpx>
GPX
        , Geocoder::VERSION);

        $this->assertNotNull($obj->getBounds());

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }
}
