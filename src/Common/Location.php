<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

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
    public function getCoordinates(): ?Coordinates;

    /**
     * Returns the bounds value object.
     *
     * @return Bounds|null
     */
    public function getBounds(): ?Bounds;

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
    public function getStreetName(): ?string;

    /**
     * Returns the city or locality value.
     *
     * @return string|null
     */
    public function getLocality(): ?string;

    /**
     * Returns the postal code or zipcode value.
     *
     * @return string|null
     */
    public function getPostalCode(): ?string;

    /**
     * Returns the locality district, or
     * sublocality, or neighborhood.
     *
     * @return string|null
     */
    public function getSubLocality(): ?string;

    /**
     * Returns the administrative levels.
     *
     * This method MUST NOT return null.
     *
     * @return AdminLevelCollection
     */
    public function getAdminLevels(): AdminLevelCollection;

    /**
     * Returns the country value object.
     *
     * @return Country|null
     */
    public function getCountry(): ?Country;

    /**
     * Returns the timezone for the Location. The timezone MUST be in the list of supported timezones.
     *
     * {@link http://php.net/manual/en/timezones.php}
     *
     * @return string|null
     */
    public function getTimezone(): ?string;

    /**
     * Returns an array with data indexed by name.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * The name of the provider that created this Location.
     *
     * @return string
     */
    public function getProvidedBy(): string;
}
