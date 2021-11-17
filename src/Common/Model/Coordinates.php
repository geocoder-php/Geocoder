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

use Geocoder\Assert;
use InvalidArgumentException;
use LogicException;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class Coordinates
{
    /**
     * @var float|null
     */
    private ?float $latitude;

    /**
     * @var float|null
     */
    private ?float $longitude;

    /**
     * @param float|null $latitude
     * @param float|null $longitude
     */
    public function __construct($latitude = null, $longitude = null)
    {
        if (!is_null($latitude)) {
            $latitude = floatval($latitude);
        }

        if (!is_null($longitude)) {
            $longitude = floatval($longitude);
        }

        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @return bool
     */
    public function hasLatitude(): bool
    {
        try {
            Assert::latitude($this->latitude);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        return !is_null($this->latitude);
    }

    /**
     * Returns the latitude.
     *
     * @return float
     */
    public function getLatitude(): float
    {
        Assert::latitude($this->latitude);

        if (is_null($this->latitude)) {
            throw new LogicException('Latitude is not set');
        }

        return $this->latitude;
    }

    /**
     * @return bool
     */
    public function hasLongitude(): bool
    {
        try {
            Assert::longitude($this->longitude);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        return !is_null($this->longitude);
    }

    /**
     * Returns the longitude.
     *
     * @return float
     */
    public function getLongitude(): float
    {
        Assert::longitude($this->longitude);

        if (is_null($this->longitude)) {
            throw new LogicException('Longitude is not set');
        }

        return $this->longitude;
    }

    /**
     * Returns the coordinates as a tuple.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [$this->getLongitude(), $this->getLatitude()];
    }
}
