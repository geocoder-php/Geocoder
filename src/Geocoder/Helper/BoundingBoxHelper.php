<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Helper;

use Geocoder\Geocoder;
use Geocoder\Result\Geocoded;

/**
 * @author Ronan Guilloux <ronan.guilloux@gmail.com>
 */
class BoundingBoxHelper implements HelperInterface
{
    /**
     * {@inheritdoc}
     */
    public function help()
    {
        return <<<EOF

    getAngle: Get a boundingBox angle geopoint from a lat;long tuple, a bearing angle & a distance in km/miles

    Inspired from richardpeacock.com
    Figure out the corners of a box surrounding our lat/lng.
    bearing is 0 = north, 180 = south, 90 = east, 270 = west
    45, 135, 225, 315 are the squared bounding box's bearing angles

        $d = 500;  // distance
        $obj = new Geocoded();
        $obj->fromArray(array('latitude'=>'47.218371', 'longitude'=>'-1.553621'));
        $northEast = BoundingBoxHelper::getAngle($geocoded, 45, $d);
        $southEast = BoundingBoxHelper::getAngle($geocoded, 135, $d);
        $southWest = BoundingBoxHelper::getAngle($geocoded, 225, $d);
        $northWest = BoundingBoxHelper::getAngle($geocoded, 315, $d);

    Googlemap check:

        $northEastGmap = "https://maps.google.com/maps?f=q&q=";
        $northEastGmap .= sprintf("%F",$geocoded->getLatitude());
        $northEastGmap .= ",";
        $northEastGmap .= sprintf("%F",$geocoded->getLongitude());
        $northEastGmap .= "&z=10";
        echo $northEastGmap;

EOF;
    }

    /**
     * getAngle: Get a bounding box angle
     * Determining two angles (southWest & northEast) is sufficient to create a boundinx box
     *
     * @link http://richardpeacock.com/blog/2011/11/draw-box-around-coordinate-google-maps-based-miles-or-kilometers
     * @link http://www.sitepoint.com/forums/showthread.php?656315-adding-distance-gps-coordinates-get-bounding-box
     * @param  ResultInterface $result
     * @param  integer         $bearing
     * @param  float           $distance
     * @param  string          $distance_unit
     * @return Geocoded        A bounding box Angle
     */
    public static function getAngle(Geocoded $result, $bearing, $distance, $distance_unit = "km")
    {
        $geocoded = new Geocoded();

        if ($distance_unit == "m") { // Distance is in miles.
            $radius = 3963.1676;
        } else { // distance is in km.
            $radius = 6378.1;
        }

        $tuple = array();
        // New latitude in degrees.
        $tuple['latitude'] = rad2deg(asin(sin(deg2rad($result->getLatitude())) * cos($distance / $radius) + cos(deg2rad($result->getLatitude())) * sin($distance / $radius) * cos(deg2rad($bearing))));

        // New longitude in degrees.
        $tuple['longitude'] = rad2deg(deg2rad($result->getLongitude()) + atan2(sin(deg2rad($bearing)) * sin($distance / $radius) * cos(deg2rad($result->getLatitude())), cos($distance / $radius) - sin(deg2rad($result->getLatitude())) * sin(deg2rad($tuple['latitude']))));

        $geocoded->fromArray($tuple);

            return $geocoded;
    }

}
