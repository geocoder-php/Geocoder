<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Dumper;

use Geocoder\Geocoder;
use Geocoder\Model\Address;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class Gpx implements Dumper
{
    /**
     * @param Address $address
     *
     * @return string
     */
    public function dump(Address $address)
    {
        $gpx = sprintf(<<<GPX
<?xml version="1.0" encoding="UTF-8" standalone="no" ?>
<gpx
version="1.0"
    creator="Geocoder" version="%s"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.topografix.com/GPX/1/0"
    xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd">

GPX
        , Geocoder::VERSION);

        if ($address->getBounds()->isDefined()) {
            $bounds = $address->getBounds();
            $gpx .= sprintf(<<<GPX
    <bounds minlat="%f" minlon="%f" maxlat="%f" maxlon="%f"/>

GPX
            , $bounds->getWest(), $bounds->getSouth(), $bounds->getEast(), $bounds->getNorth());
        }

        $gpx .= sprintf(<<<GPX
    <wpt lat="%.7f" lon="%.7f">
        <name><![CDATA[%s]]></name>
        <type><![CDATA[Address]]></type>
    </wpt>

GPX
        , $address->getLatitude(), $address->getLongitude(), $this->formatName($address));

        $gpx .= <<<GPX
</gpx>
GPX;

        return $gpx;
    }

    /**
     * @param Address $address
     *
     * @return string
     */
    protected function formatName(Address $address)
    {
        $name  = [];
        $array = $address->toArray();
        $attrs = [
            ['streetNumber'],
            ['streetName'],
            ['postalCode'],
            ['locality'],
            ['adminLevels', 2, 'name'],
            ['adminLevels', 1, 'name'],
            ['country'],
        ];

        foreach ($attrs as $attr) {
            $name[] = \igorw\get_in($array, $attr);
        }

        return implode(', ', array_filter($name));
    }
}
