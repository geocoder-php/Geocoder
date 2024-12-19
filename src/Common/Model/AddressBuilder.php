<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Model;

use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\LogicException;

/**
 * A class that builds a Location or any of its subclasses.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class AddressBuilder
{
    /**
     * @var string
     */
    private $providedBy;

    /**
     * @var Coordinates|null
     */
    private $coordinates;

    /**
     * @var Bounds|null
     */
    private $bounds;

    /**
     * @var string|null
     */
    private $streetNumber;

    /**
     * @var string|null
     */
    private $streetName;

    /**
     * @var string|null
     */
    private $locality;

    /**
     * @var string|null
     */
    private $postalCode;

    /**
     * @var string|null
     */
    private $subLocality;

    /**
     * @var AdminLevel[]
     */
    private $adminLevels = [];

    /**
     * @var string|null
     */
    private $country;

    /**
     * @var string|null
     */
    private $countryCode;

    /**
     * @var string|null
     */
    private $timezone;

    /**
     * A storage for extra parameters.
     *
     * @var array<string, mixed>
     */
    private $data = [];

    public function __construct(string $providedBy)
    {
        $this->providedBy = $providedBy;
    }

    public function build(string $class = Address::class): Address
    {
        if (!is_a($class, Address::class, true)) {
            throw new LogicException('First parameter to LocationBuilder::build must be a class name extending Geocoder\Model\Address');
        }

        $country = null;
        if ((null !== $this->country && '' !== $this->country) || (null !== $this->countryCode && '' !== $this->countryCode)) {
            $country = new Country($this->country, $this->countryCode);
        }

        return new $class(
            $this->providedBy,
            new AdminLevelCollection($this->adminLevels),
            $this->coordinates,
            $this->bounds,
            $this->streetNumber,
            $this->streetName,
            $this->postalCode,
            $this->locality,
            $this->subLocality,
            $country,
            $this->timezone
        );
    }

    /**
     * @param float $south
     * @param float $west
     * @param float $north
     * @param float $east
     */
    public function setBounds($south, $west, $north, $east): self
    {
        try {
            $this->bounds = new Bounds($south, $west, $north, $east);
        } catch (InvalidArgument $e) {
            $this->bounds = null;
        }

        return $this;
    }

    /**
     * @param float $latitude
     * @param float $longitude
     */
    public function setCoordinates($latitude, $longitude): self
    {
        try {
            $this->coordinates = new Coordinates($latitude, $longitude);
        } catch (InvalidArgument $e) {
            $this->coordinates = null;
        }

        return $this;
    }

    public function addAdminLevel(int $level, string $name, ?string $code = null): self
    {
        $this->adminLevels[] = new AdminLevel($level, $name, $code);

        return $this;
    }

    /**
     * @param string|null $streetNumber
     */
    public function setStreetNumber($streetNumber): self
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    /**
     * @param string|null $streetName
     */
    public function setStreetName($streetName): self
    {
        $this->streetName = $streetName;

        return $this;
    }

    /**
     * @param string|null $locality
     */
    public function setLocality($locality): self
    {
        $this->locality = $locality;

        return $this;
    }

    /**
     * @param string|null $postalCode
     */
    public function setPostalCode($postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @param string|null $subLocality
     */
    public function setSubLocality($subLocality): self
    {
        $this->subLocality = $subLocality;

        return $this;
    }

    /**
     * @param AdminLevel[] $adminLevels
     */
    public function setAdminLevels($adminLevels): self
    {
        $this->adminLevels = $adminLevels;

        return $this;
    }

    /**
     * @param string|null $country
     */
    public function setCountry($country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @param string|null $countryCode
     */
    public function setCountryCode($countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @param string|null $timezone
     */
    public function setTimezone($timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function setValue(string $name, mixed $value): self
    {
        $this->data[$name] = $value;

        return $this;
    }

    public function getValue(string $name, mixed $default = null): mixed
    {
        if ($this->hasValue($name)) {
            return $this->data[$name];
        }

        return $default;
    }

    public function hasValue(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }
}
