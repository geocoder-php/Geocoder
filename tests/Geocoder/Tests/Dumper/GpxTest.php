<?php

namespace Geocoder\Tests\Dumper;

use Geocoder\Geocoder;
use Geocoder\Dumper\Gpx;
use Geocoder\Tests\TestCase;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class GpxTest extends TestCase
{
    private $dumper;

    public function setUp()
    {
        $this->dumper = new Gpx();
    }

    public function testDump()
    {
        $address  = $this->createAddress([]);
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

        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithData()
    {
        $address = $this->createAddress([
            'latitude'  => 48.8631507,
            'longitude' => 2.3889114,
        ]);

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
        , Geocoder::VERSION, $address->getCoordinates()->getLatitude(), $address->getCoordinates()->getLongitude());

        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }

    public function testDumpWithBounds()
    {
        $address = $this->createAddress([
            'latitude'  => 48.8631507,
            'longitude' => 2.3889114,
            'bounds' => [
                'south' => 48.8631507,
                'west'  => 2.3889114,
                'north' => 48.8631507,
                'east'  => 2.388911
            ]
        ]);
        $bounds = $address->getBounds()->toArray();

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

        $this->assertNotNull($address->getBounds());

        $result = $this->dumper->dump($address);

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

        $address = $this->createAddress([
            'latitude'     => 48.8631507,
            'longitude'    => 2.3889114,
            'bounds'       => $bounds,
            'locality'     => 'Paris',
            'streetName'   => 'Avenue Gambetta',
            'streetNumber' => '10',
            'subLocality'  => '20e Arrondissement',
            'adminLevels'  => [['level' => 1, 'name' => 'Ile-de-France']],
            'country'      => 'France'
        ]);

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
        <name><![CDATA[10, Avenue Gambetta, Paris, Ile-de-France, France]]></name>
        <type><![CDATA[Address]]></type>
    </wpt>
</gpx>
GPX
        , Geocoder::VERSION, $bounds['east'],"48.863151", $bounds['east'], "48.863151", $bounds['north'], $bounds['west']);

        $this->assertNotNull($address->getBounds());

        $result = $this->dumper->dump($address);

        $this->assertTrue(is_string($result));
        $this->assertEquals($expected, $result);
    }
}
