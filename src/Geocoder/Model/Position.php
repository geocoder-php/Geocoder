<?php
namespace Geocoder\Model;

/**
 * A position is a single result from a Geocoder.
 *
 * @author William Durand <william.durand1@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface Position
{
    /**
     * Returns an array of coordinates (latitude, longitude).
     *
     * @return Coordinates
     */
    public function getCoordinates();

    /**
     * Returns the latitude value.
     *
     * @return double
     */
    public function getLatitude();

    /**
     * Returns the longitude value.
     *
     * @return double
     */
    public function getLongitude();

    /**
     * Returns the bounds value.
     *
     * @return Bounds
     */
    public function getBounds();

    /**
     * Returns the street number value.
     *
     * @return string|int
     */
    public function getStreetNumber();

    /**
     * Returns the street name value.
     *
     * @return string
     */
    public function getStreetName();

    /**
     * Returns the city or locality value.
     *
     * @return string
     */
    public function getLocality();

    /**
     * Returns the postal code or zipcode value.
     *
     * @return string
     */
    public function getPostalCode();

    /**
     * Returns the locality district, or
     * sublocality, or neighborhood.
     *
     * @return string
     */
    public function getSubLocality();

    /**
     * Returns the administrative levels.
     *
     * @return AdminLevelCollectionInterface
     */
    public function getAdminLevels();

    /**
     * Returns the country value.
     *
     * @return Country
     */
    public function getCountry();

    /**
     * Returns the country ISO code.
     *
     * @return string
     */
    public function getCountryCode();

    /**
     * Returns the timezone.
     *
     * @return string
     */
    public function getTimezone();

    /**
     * Returns an array with data indexed by name.
     *
     * @return array
     */
    public function toArray();
}