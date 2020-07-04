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
     * @return null|int
     */
    public function getOSMId()
    {
        return $this->osmId;
    }

    /**
     * @param null|int $osmId
     *
     * @return PhotonAddress
     */
    public function withOSMId(?int $osmId = null): self
    {
        $new = clone $this;
        $new->osmId = $osmId;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getOSMType(): ?string
    {
        return $this->osmType;
    }

    /**
     * @param null|string $osmType
     *
     * @return PhotonAddress
     */
    public function withOSMType(?string $osmType = null): self
    {
        $new = clone $this;
        $new->osmType = $osmType;

        return $new;
    }

    /**
     * @return null|object
     */
    public function getOSMTag()
    {
        return $this->osmTag;
    }

    /**
     * @param null|string $key
     * @param null|string $value
     *
     * @return PhotonAddress
     */
    public function withOSMTag(?string $key = null, ?string $value = null): self
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
}
