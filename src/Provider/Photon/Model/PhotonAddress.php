<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Photon\Model;

use Geocoder\Model\Address;

/**
 * @author Jonathan Beliën <jbe@geo6.be>
 */
final class PhotonAddress extends Address
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var int|null
     */
    private $osmId;

    /**
     * @var string|null
     */
    private $osmType;

    /**
     * @var \stdclass|null
     */
    private $osmTag;

    /**
     * @var string|null
     */
    private $state;

    /**
     * @var string|null
     */
    private $county;

    /**
     * @var string|null
     */
    private $district;

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return PhotonAddress
     */
    public function withName(string $name = null): self
    {
        $new = clone $this;
        $new->name = $name;

        return $new;
    }

    /**
     * @return int|null
     */
    public function getOSMId()
    {
        return $this->osmId;
    }

    /**
     * @param int|null $osmId
     *
     * @return PhotonAddress
     */
    public function withOSMId(int $osmId = null): self
    {
        $new = clone $this;
        $new->osmId = $osmId;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getOSMType()
    {
        return $this->osmType;
    }

    /**
     * @param string|null $osmType
     *
     * @return PhotonAddress
     */
    public function withOSMType(string $osmType = null): self
    {
        $new = clone $this;
        $new->osmType = $osmType;

        return $new;
    }

    /**
     * @return object|null
     */
    public function getOSMTag()
    {
        return $this->osmTag;
    }

    /**
     * @param string|null $key
     * @param string|null $value
     *
     * @return PhotonAddress
     */
    public function withOSMTag(string $key = null, string $value = null): self
    {
        $new = clone $this;

        if (!is_null($key) && !is_null($value)) {
            $new->osmTag = (object) [
                'key' => $key,
                'value' => $value,
            ];
        } else {
            $new->osmTag = null;
        }

        return $new;
    }

    /**
     * @return string|null
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     * @return PhotonAddress
     */
    public function withState(string $state = null): self
    {
        $new = clone $this;
        $new->state = $state;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * @param string|null $county
     * @return PhotonAddress
     */
    public function withCounty(string $county = null): self
    {
        $new = clone $this;
        $new->county = $county;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * @param string|null $district
     * @return PhotonAddress
     */
    public function withDistrict(string $district = null): self
    {
        $new = clone $this;
        $new->district = $district;

        return $new;
    }
}
