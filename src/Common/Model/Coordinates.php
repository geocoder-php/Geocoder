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

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class Coordinates
{
    /**
     * @var float
     */
    private $latitude;

    /**
     * @var float
     */
    private $longitude;

    /**
     * @param  float|null  $latitude
     * @param  float|null  $longitude
     */
    public function __construct(?float $latitude = null, ?float $longitude = null)
    {
        Assert::latitude($latitude);
        Assert::longitude($longitude);

        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @return bool
     */
    public function hasLatitude(): bool
    {
        return !is_null($this->latitude);
    }

    /**
     * Returns the latitude.
     *
     * @return float
     */
    public function getLatitude(): float
    {
        if (is_null($this->latitude)) {
            throw new \LogicException('Latitude is not set');
        }

        return $this->latitude;
    }

    /**
     * @return bool
     */
    public function hasLongitude(): bool
    {
        return !is_null($this->longitude);
    }

    /**
     * Returns the longitude.
     *
     * @return float
     */
    public function getLongitude(): float
    {
        if (is_null($this->longitude)) {
            throw new \LogicException('Longitude is not set');
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
