<?php
namespace Geocoder\Model;


/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface BoundsInterface
{
    /**
     * Returns the south bound.
     *
     * @return double
     */
    public function getSouth();

    /**
     * Returns the west bound.
     *
     * @return double
     */
    public function getWest();

    /**
     * Returns the north bound.
     *
     * @return double
     */
    public function getNorth();

    /**
     * Returns the east bound.
     *
     * @return double
     */
    public function getEast();

    /**
     * Returns whether or not bounds are defined
     *
     * @return bool
     */
    public function isDefined();

    /**
     * Returns an array with bounds. The array MUST have the following keys: south, west, north, east
     *
     * @return array
     */
    public function toArray();
}