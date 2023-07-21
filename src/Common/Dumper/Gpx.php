<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Dumper;

use Geocoder\Geocoder;
use Geocoder\Location;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class Gpx extends AbstractDumper implements Dumper
{
    public function dump(Location $location): string
    {
        $gpx = sprintf(<<<'GPX'
<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
<gpx
version="1.0"
    creator="Geocoder" version="%s"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/0"
    xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd">

GPX
            , Geocoder::VERSION);

        if (null !== $bounds = $location->getBounds()) {
            $gpx .= sprintf(<<<'GPX'
    <bounds minlat="%f" minlon="%f" maxlat="%f" maxlon="%f"/>

GPX
                , $bounds->getWest(), $bounds->getSouth(), $bounds->getEast(), $bounds->getNorth());
        }

        $lat = null;
        $lon = null;
        if (null !== $coordinates = $location->getCoordinates()) {
            $lat = $coordinates->getLatitude();
            $lon = $coordinates->getLongitude();
        }

        $gpx .= sprintf(<<<'GPX'
    <wpt lat="%.7f" lon="%.7f">
        <name><![CDATA[%s]]></name>
        <type><![CDATA[Address]]></type>
    </wpt>

GPX
            , $lat, $lon, $this->formatName($location));

        $gpx .= <<<'GPX'
</gpx>
GPX;

        return $gpx;
    }
}
