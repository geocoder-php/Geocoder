<?php

namespace Geocoder\Tests\Dumper;

use Geocoder\Geocoder;
use Geocoder\Dumper\GpxDumper;
use Geocoder\Result\Geocoded;
use Geocoder\Result\ResultInterface;
use Geocoder\Tests\TestCase;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class GpxDumperTest extends TestCase
{
    private $dumper;

    public function setUp()
    {
        $this->dumper = new TestableGpxDumper();
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
    <wpt lat="%01.7f" lon="%01.7f">
        <name><![CDATA[]]></name>
        <type><![CDATA[Address]]></type>
    </wpt>
</gpx>
GPX
        , Geocoder::VERSION, "0", "0");

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
    <wpt lat="%01.7f" lon="%01.7f">
        <name><![CDATA[]]></name>
        <type><![CDATA[Address]]></type>
    </wpt>
</gpx>
GPX
        , Geocoder::VERSION, $obj['latitude'], $obj['longitude']);

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithBounds()
    {
        $bounds = array('south' => 48.8631507,
                'west'  => 2.3889114,
                'north' => 48.8631507,
                'east'  => 2.388911);

        $obj = new Geocoded();
        $obj['latitude']  = $bounds['north'];
        $obj['longitude'] = $bounds['west'];
        $obj->fromArray(array(
            'bounds' => $bounds
        ));

        $expected = sprintf(<<<GPX
<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
<gpx
version="1.0"
    creator="Geocoder" version="%s"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/0"
    xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd">
    <bounds minlat="%01.6f" minlon="%01.6f" maxlat="%01.6f" maxlon="%01.6f"/>
    <wpt lat="%01.7f" lon="%01.7f">
        <name><![CDATA[]]></name>
        <type><![CDATA[Address]]></type>
    </wpt>
</gpx>
GPX
        , Geocoder::VERSION, $bounds['east'],  "48.863151", $bounds['east'], "48.863151", $bounds['north'], $bounds['west']);

        $this->assertNotNull($obj->getBounds());

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithName()
    {
        $bounds = array(
            'south' => 48.8631507,
            'west'  => 2.3889114,
            'north' => 48.8631507,
            'east'  => 2.388911);

        $obj = new Geocoded();
        $obj['latitude']  = 48.8631507;
        $obj['longitude'] = 2.3889114;
        $obj->fromArray(array(
            'bounds' => $bounds,
            'city'   => 'Paris'
        ));

        $expected = sprintf(<<<GPX
<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
<gpx
version="1.0"
    creator="Geocoder" version="%s"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/0"
    xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd">
    <bounds minlat="%01.6f" minlon="%01.6f" maxlat="%01.6f" maxlon="%01.6f"/>
    <wpt lat="%01.7f" lon="%01.7f">
        <name><![CDATA[Paris]]></name>
        <type><![CDATA[Address]]></type>
    </wpt>
</gpx>
GPX
        , Geocoder::VERSION, $bounds['east'],"48.863151", $bounds['east'], "48.863151", $bounds['north'], $bounds['west']);

        $this->assertNotNull($obj->getBounds());

        $result = $this->dumper->dump($obj);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    public function testFormatName()
    {
        $obj = new Geocoded();
        $obj->fromArray(array(
            'city' => 'Paris',
        ));

        $expected = 'Paris';

        $this->assertEquals($expected, $this->dumper->formatName($obj));
    }

    public function testFormatNameWithMultipleValues()
    {
        $obj = new Geocoded();
        $obj->fromArray(array(
            'city'      => 'Paris',
            'country'   => 'France',
        ));

        $expected = 'Paris, France';

        $this->assertEquals($expected, $this->dumper->formatName($obj));
    }

    public function testFormatNameDoesNotAddUnrecognizedParameters()
    {
        $obj = new Geocoded();
        $obj->fromArray(array(
            'city'      => 'Paris',
            'country'   => 'France',
            'foo'       => 'foo',
            'bar'       => 'bar',
        ));

        $expected = 'Paris, France';

        $this->assertEquals($expected, $this->dumper->formatName($obj));
    }
}

class TestableGpxDumper extends GpxDumper
{
    public function formatName(ResultInterface $result)
    {
        return parent::formatName($result);
    }
}
