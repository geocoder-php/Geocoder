<?php
namespace Geocoder;

use Geocoder\Model\AdminLevelCollection;
use Geocoder\Model\Bounds;
use Geocoder\Model\Coordinates;
use Geocoder\Model\Country;

/**
 * A location is a single result from a Geocoder.
 *
 * @author William Durand <william.durand1@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface Location
{
    /**
     * Will always return the coordinates value object.
     *
     * @return Coordinates|null
     */
    public function getCoordinates();

    /**
     * Returns the bounds value object.
     *
     * @return Bounds|null
     */
    public function getBounds();

    /**
     * Returns the street number value.
     *
     * @return string|int|null
     */
    public function getStreetNumber();

    /**
     * Returns the street name value.
     *
     * @return string|null
     */
    public function getStreetName();

    /**
     * Returns the city or locality value.
     *
     * @return string|null
     */
    public function getLocality();

    /**
     * Returns the postal code or zipcode value.
     *
     * @return string|null
     */
    public function getPostalCode();

    /**
     * Returns the locality district, or
     * sublocality, or neighborhood.
     *
     * @return string|null
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
     * @return Country|null
     */
    public function getCountry();

    /**
     * Returns the timezone for the Location. The timezone MUST be in the list of supported timezones.
     *
     * {@link http://php.net/manual/en/timezones.php}
     *
     * @return string|null
     */
    public function getTimezone();

    /**
     * Returns an array with data indexed by name.
     *
     * @return array
     */
    public function toArray();
}