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
     *
     * @see https://en.wikipedia.org/wiki/Military_Grid_Reference_System
     */
    private $mgrs;

    /**
     * @var string|null
     *
     * @see https://en.wikipedia.org/wiki/Maidenhead_Locator_System
     */
    private $maidenhead;

    /**
     * @var string|null
     *
     * @see https://en.wikipedia.org/wiki/Geohash
     */
    private $geohash;

    /**
     * @var string|null
     *
     * @see https://what3words.com/
     */
    private $what3words;

    /**
     * @var string|null
     */
    private $formattedAddress;

    public function withMGRS(?string $mgrs = null): self
    {
        $new = clone $this;
        $new->mgrs = $mgrs;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getMGRS()
    {
        return $this->mgrs;
    }

    public function withMaidenhead(?string $maidenhead = null): self
    {
        $new = clone $this;
        $new->maidenhead = $maidenhead;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getMaidenhead()
    {
        return $this->maidenhead;
    }

    public function withGeohash(?string $geohash = null): self
    {
        $new = clone $this;
        $new->geohash = $geohash;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getGeohash()
    {
        return $this->geohash;
    }

    public function withWhat3words(?string $what3words = null): self
    {
        $new = clone $this;
        $new->what3words = $what3words;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getWhat3words()
    {
        return $this->what3words;
    }

    public function withFormattedAddress(?string $formattedAddress = null): self
    {
        $new = clone $this;
        $new->formattedAddress = $formattedAddress;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getFormattedAddress()
    {
        return $this->formattedAddress;
    }
}
