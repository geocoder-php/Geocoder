<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Dumper;

use Geocoder\Location;

/**
 * @author Jan Sorgalla <jsorgalla@googlemail.com>
 */
class Kml extends Gpx implements Dumper
{
    /**
     * {@inheritDoc}
     */
    public function dump(Location $location)
    {
        $name = $this->formatName($location);
        $kml  = <<<KML
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
    <Document>
        <Placemark>
            <name><![CDATA[%s]]></name>
            <description><![CDATA[%s]]></description>
            <Point>
                <coordinates>%.7F,%.7F,0</coordinates>
            </Point>
        </Placemark>
    </Document>
</kml>
KML;

        $lat = null;
        $lon = null;
        if (null !== $coordinates = $location->getCoordinates()) {
            $lat = $coordinates->getLatitude();
            $lon = $coordinates->getLongitude();
        }

        return sprintf($kml, $name, $name, $lon, $lat);
    }
}
