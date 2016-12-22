<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Model;

use Geocoder\Assert;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class Coordinates
{
    /**
     * @var double
     */
    private $latitude;

    /**
     * @var double
     */
    private $longitude;

    /**
     * @param double $latitude
     * @param double $longitude
     */
    public function __construct($latitude, $longitude)
    {
        $latitude = (double) $latitude;
        $longitude = (double) $longitude;

        Assert::latitude($latitude);
        Assert::longitude($longitude);

        $this->latitude  = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Returns the latitude.
     *
     * @return double|null
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Returns the longitude.
     *
     * @return double|null
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
}
