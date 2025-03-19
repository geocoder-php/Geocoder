<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Pelias\Model;

use Geocoder\Model\Address;

/**
 * @author Jonathan Beliën <jbelien@users.noreply.github.com>
 */
final class PeliasAddress extends Address
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $layer;

    /**
     * @var string|null
     */
    private $source;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var float
     */
    private $confidence;

    /**
     * @var string|null
     */
    private $accuracy;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function withId(?string $id): self
    {
        $new = clone $this;
        $new->id = $id;

        return $new;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function withSource(?string $source): self
    {
        $new = clone $this;
        $new->source = $source;

        return $new;
    }

    public function getLayer(): string
    {
        return $this->layer;
    }

    public function withLayer(?string $layer): self
    {
        $new = clone $this;
        $new->layer = $layer;

        return $new;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function withName(?string $name): self
    {
        $new = clone $this;
        $new->name = $name;

        return $new;
    }

    public function getConfidence(): float
    {
        return $this->confidence;
    }

    public function withConfidence(float $confidence): self
    {
        $new = clone $this;
        $new->confidence = $confidence;

        return $new;
    }

    public function getAccuracy(): ?string
    {
        return $this->accuracy;
    }

    public function withAccuracy(?string $accuracy): self
    {
        $new = clone $this;
        $new->accuracy = $accuracy;

        return $new;
    }
}
