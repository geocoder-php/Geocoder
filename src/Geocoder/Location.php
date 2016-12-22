<?php
namespace Geocoder;

use Geocoder\Model\AdminLevelCollection;
use Geocoder\Model\Bounds;
use Geocoder\Model\Coordinates;
use Geocoder\Model\Country;

/**
 * A position is a single result from a Geocoder.
 *
 * @author William Durand <william.durand1@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface Location
{
    /**
     * Will always return the coordinates value object.
     *
     * This method MUST NOT return null.
     *
     * @return Coordinates
     */
    public function getCoordinates();

    /**
     * Returns the bounds value object.
     *
     * This method MUST NOT return null.
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
     * This method MUST NOT return null.
     *
     * @return AdminLevelCollection
     */
    public function getAdminLevels();

    /**
     * Returns the country value object.
     *
     * This method MUST NOT return null.
     *
     * @return Country
     */
    public function getCountry();

    /**
     * Returns the timezone for the Position. The timezone MUST be in the list of supported timezones.
     *
     * {@link http://php.net/manual/en/timezones.php}
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