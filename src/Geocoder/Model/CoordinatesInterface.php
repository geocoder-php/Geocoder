<?php
namespace Geocoder\Model;


/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface CoordinatesInterface
{
    /**
     * Returns the latitude.
     *
     * @return double
     */
    public function getLatitude();

    /**
     * Returns the longitude.
     *
     * @return double
     */
    public function getLongitude();
}