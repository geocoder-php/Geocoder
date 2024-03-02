<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Mapbox\Model;

use Geocoder\Model\Address;

final class MapboxAddress extends Address
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|int|null
     */
    private $streetNumber;

    /**
     * @var string|null
     */
    private $streetName;

    /**
     * @var string[]
     */
    private $resultType = [];

    /**
     * @var string|null
     */
    private $formattedAddress;

    /**
     * @var string|null
     */
    private $neighborhood;

    public function withId(?string $id = null): self
    {
        $new = clone $this;
        $new->id = $id;

        return $new;
    }

    /**
     * @see https://www.mapbox.com/api-documentation/?language=cURL#response-object
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function withStreetName(?string $streetName = null): self
    {
        $new = clone $this;
        $new->streetName = $streetName;

        return $new;
    }

    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    public function withStreetNumber(?string $streetNumber = null): self
    {
        $new = clone $this;
        $new->streetNumber = $streetNumber;

        return $new;
    }

    /**
     * @return string[]
     */
    public function getResultType(): array
    {
        return $this->resultType;
    }

    /**
     * @param string[] $resultType
     */
    public function withResultType(array $resultType): self
    {
        $new = clone $this;
        $new->resultType = $resultType;

        return $new;
    }

    public function getFormattedAddress(): ?string
    {
        return $this->formattedAddress;
    }

    public function withFormattedAddress(?string $formattedAddress = null): self
    {
        $new = clone $this;
        $new->formattedAddress = $formattedAddress;

        return $new;
    }

    public function getNeighborhood(): ?string
    {
        return $this->neighborhood;
    }

    public function withNeighborhood(?string $neighborhood = null): self
    {
        $new = clone $this;
        $new->neighborhood = $neighborhood;

        return $new;
    }
}
