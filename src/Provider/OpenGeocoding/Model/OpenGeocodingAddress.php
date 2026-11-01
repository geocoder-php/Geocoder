<?php

declare(strict_types=1);

/*
 * This file is part of the OpenGeocoding provider for Geocoder PHP.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\OpenGeocoding\Model;

use Geocoder\Model\Address;

final class OpenGeocodingAddress extends Address
{
    private ?string $displayName = null;

    private bool $goodMatch = false;

    private ?float $distance = null;

    private ?string $layer = null;

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function withDisplayName(?string $displayName): self
    {
        $new = clone $this;
        $new->displayName = $displayName;

        return $new;
    }

    /**
     * False for a partial match, such as the street when the house number was not found.
     */
    public function isGoodMatch(): bool
    {
        return $this->goodMatch;
    }

    public function withGoodMatch(bool $goodMatch): self
    {
        $new = clone $this;
        $new->goodMatch = $goodMatch;

        return $new;
    }

    /**
     * Distance in metres from the queried point; reverse geocoding only.
     */
    public function getDistance(): ?float
    {
        return $this->distance;
    }

    public function withDistance(?float $distance): self
    {
        $new = clone $this;
        $new->distance = $distance;

        return $new;
    }

    /**
     * Reverse geocoding only: "address", "street" or "locality".
     */
    public function getLayer(): ?string
    {
        return $this->layer;
    }

    public function withLayer(?string $layer): self
    {
        $new = clone $this;
        $new->layer = $layer;

        return $new;
    }
}
