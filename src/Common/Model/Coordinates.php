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
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct($latitude, $longitude)
    {
        Assert::notNull($latitude);
        Assert::notNull($longitude);

        $latitude = (float) $latitude;
        $longitude = (float) $longitude;

        Assert::latitude($latitude);
        Assert::longitude($longitude);

        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Returns the latitude.
     *
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * Returns the longitude.
     *
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * Returns the coordinates as a tuple
     *
     * @return array
     */
    public function toArray(): array
    {
        return [$this->getLongitude(), $this->getLatitude()];
    }
}
