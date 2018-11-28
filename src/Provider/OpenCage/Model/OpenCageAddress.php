<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\OpenCage\Model;

use Geocoder\Model\Address;

final class OpenCageAddress extends Address
{
    /**
     * @var string|null
     */
    private $geohash;

    /**
     * @var string|null
     */
    private $formattedAddress;

    /**
     * @param null|string $geohash
     *
     * @return OpenCageAddress
     */
    public function withGeohash(string $geohash = null): self
    {
        $new = clone $this;
        $new->geohash = $geohash;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getGeohash()
    {
        return $this->geohash;
    }

    /**
     * @param string|null $formattedAddress
     *
     * @return OpenCageAddress
     */
    public function withFormattedAddress(string $formattedAddress = null): self
    {
        $new = clone $this;
        $new->formattedAddress = $formattedAddress;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getFormattedAddress()
    {
        return $this->formattedAddress;
    }
}
