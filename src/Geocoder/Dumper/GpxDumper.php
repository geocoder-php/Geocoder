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
use Geocoder\Result\ResultInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class GpxDumper implements DumperInterface
{
    /**
     * @param ResultInterface $result
     *
     * @return string
     */
    public function dump(ResultInterface $result)
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

        if ($bounds = $result->getBounds()) {
            $gpx .= sprintf(<<<GPX
    <bounds minlat="%f" minlon="%f" maxlat="%f" maxlon="%f"/>

GPX
            , $bounds['west'], $bounds['south'], $bounds['east'], $bounds['north']);
        }

        $gpx .= sprintf(<<<GPX
    <wpt lat="%.7f" lon="%.7f">
        <name><![CDATA[%s]]></name>
        <type><![CDATA[Address]]></type>
    </wpt>

GPX
        , $result->getLatitude(), $result->getLongitude(), $this->formatName($result));

        $gpx .= <<<GPX
</gpx>
GPX;

        return $gpx;
    }

    /**
     * @param ResultInterface $result
     *
     * @return string
     */
    protected function formatName(ResultInterface $result)
    {
        $name  = '';
        $array = $result->toArray();
        $attrs = array('streetNumber', 'streetName', 'zipcode', 'city', 'county', 'region', 'country');

        foreach ($attrs as $attr) {
            if (isset($array[$attr]) && !empty($array[$attr])) {
                $name .= sprintf('%s, ', $array[$attr]);
            }
        }

        if (strlen($name) > 1) {
            $name = substr($name, 0, strlen($name) - 2);
        }

        return $name;
    }
}
