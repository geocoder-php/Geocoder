<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Nominatim\Model;

use Geocoder\Model\Address;

/**
 * @author Jonathan Beliën <jbe@geo6.be>
 */
final class NominatimAddress extends Address
{
    /**
     * @var string|null
     */
    private $attribution;

    /**
     * @var string|null
     */
    private $category;

    /**
     * @var string|null
     */
    private $displayName;

    /**
     * @var string|null
     */
    private $quarter;

    /**
     * @var string|null
     */
    private $osmType;

    /**
     * @var int|null
     */
    private $osmId;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var array<string, mixed>|null
     */
    private $details;

    /**
     * @var array<string, mixed>|null
     */
    private $tags;

    /**
     * @var string|null
     */
    private $neighbourhood;

    /**
     * @return string|null
     */
    public function getAttribution()
    {
        return $this->attribution;
    }

    public function withAttribution(?string $attribution = null): self
    {
        $new = clone $this;
        $new->attribution = $attribution;

        return $new;
    }

    /**
     * @deprecated
     *
     * @return string|null
     */
    public function getClass()
    {
        return $this->getCategory();
    }

    /**
     * @deprecated
     */
    public function withClass(?string $category = null): self
    {
        return $this->withCategory($category);
    }

    /**
     * @return string|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    public function withCategory(?string $category = null): self
    {
        $new = clone $this;
        $new->category = $category;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function withDisplayName(?string $displayName = null): self
    {
        $new = clone $this;
        $new->displayName = $displayName;

        return $new;
    }

    /**
     * @return int|null
     */
    public function getOSMId()
    {
        return $this->osmId;
    }

    public function withOSMId(?int $osmId = null): self
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

    public function withOSMType(?string $osmType = null): self
    {
        $new = clone $this;
        $new->osmType = $osmType;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    public function withType(?string $type = null): self
    {
        $new = clone $this;
        $new->type = $type;

        return $new;
    }

    public function getQuarter(): ?string
    {
        return $this->quarter;
    }

    public function withQuarter(?string $quarter = null): self
    {
        $new = clone $this;
        $new->quarter = $quarter;

        return $new;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDetails(): ?array
    {
        return $this->details;
    }

    /**
     * @param array<string, mixed>|null $details
     */
    public function withDetails(?array $details = null): self
    {
        $new = clone $this;
        $new->details = $details;

        return $new;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getTags(): ?array
    {
        return $this->tags;
    }

    /**
     * @param array<string, mixed>|null $tags
     */
    public function withTags(?array $tags = null): self
    {
        $new = clone $this;
        $new->tags = $tags;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getNeighbourhood()
    {
        return $this->neighbourhood;
    }

    public function withNeighbourhood(?string $neighbourhood = null): self
    {
        $new = clone $this;
        $new->neighbourhood = $neighbourhood;

        return $new;
    }
}
