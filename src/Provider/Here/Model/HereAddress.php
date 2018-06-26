<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Here\Model;

use Geocoder\Model\Address;

/**
 * @author sébastien Barré <info@zoestha.de>
 */
final class HereAddress extends Address
{
    /**
     * @var string|null
     */
    private $locationId;

    /**
     * @var string|null
     */
    private $locationType;

    /**
     * @var string|null
     */
    private $locationName;

    /**
     * @return null|string
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * @param null|string $LocationId
     *
     * @return HereAddress
     */
    public function withLocationId(string $locationId = null): self
    {
        $new = clone $this;
        $new->locationId = $locationId;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getLocationType()
    {
        return $this->locationType;
    }

    /**
     * @param null|string $LocationType
     *
     * @return HereAddress
     */
    public function withLocationType(string $locationType = null): self
    {
        $new = clone $this;
        $new->locationType = $locationType;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getLocationName()
    {
        return $this->locationName;
    }

    /**
     * @param null|string $LocationName
     *
     * @return HereAddress
     */
    public function withLocationName(string $locationName = null): self
    {
        $new = clone $this;
        $new->locationName = $locationName;

        return $new;
    }
}
