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
     */
    public function getCoordinates(): ?Coordinates;

    /**
     * Returns the bounds value object.
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
     */
    public function getStreetName(): ?string;

    /**
     * Returns the city or locality value.
     */
    public function getLocality(): ?string;

    /**
     * Returns the postal code or zipcode value.
     */
    public function getPostalCode(): ?string;

    /**
     * Returns the locality district, or
     * sublocality, or neighborhood.
     */
    public function getSubLocality(): ?string;

    /**
     * Returns the administrative levels.
     *
     * This method MUST NOT return null.
     */
    public function getAdminLevels(): AdminLevelCollection;

    /**
     * Returns the country value object.
     */
    public function getCountry(): ?Country;

    /**
     * Returns the timezone for the Location. The timezone MUST be in the list of supported timezones.
     *
     * {@link http://php.net/manual/en/timezones.php}
     */
    public function getTimezone(): ?string;

    /**
     * Returns an array with data indexed by name.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * The name of the provider that created this Location.
     */
    public function getProvidedBy(): string;
}
