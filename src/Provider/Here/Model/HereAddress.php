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
     * @var array<string, mixed>|null
     */
    private $additionalData;

    /**
     * @var array<string, mixed>|null
     */
    private $shape;

    /**
     * @return string|null
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    public function withLocationId(?string $locationId = null): self
    {
        $new = clone $this;
        $new->locationId = $locationId;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getLocationType()
    {
        return $this->locationType;
    }

    public function withLocationType(?string $locationType = null): self
    {
        $new = clone $this;
        $new->locationType = $locationType;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getLocationName()
    {
        return $this->locationName;
    }

    public function withLocationName(?string $locationName = null): self
    {
        $new = clone $this;
        $new->locationName = $locationName;

        return $new;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    /**
     * @param array<string, mixed>|null $additionalData
     */
    public function withAdditionalData(?array $additionalData = null): self
    {
        $new = clone $this;

        foreach ($additionalData as $data) {
            $new = $new->addAdditionalData($data['key'], $data['value']);
        }

        return $new;
    }

    /**
     * @param mixed|null $value
     */
    public function addAdditionalData(string $name, $value = null): self
    {
        $new = clone $this;
        $new->additionalData[$name] = $value;

        return $new;
    }

    public function getAdditionalDataValue(string $name, mixed $default = null): mixed
    {
        if ($this->hasAdditionalDataValue($name)) {
            return $this->additionalData[$name];
        }

        return $default;
    }

    public function hasAdditionalDataValue(string $name): bool
    {
        return array_key_exists($name, $this->additionalData);
    }

    /**
     * @param array<string, mixed>|null $shape
     */
    public function withShape(?array $shape = null): self
    {
        $new = clone $this;

        if (!empty($shape)) {
            foreach ($shape as $key => $data) {
                $new = $new->addShape($key, $data);
            }
        }

        return $new;
    }

    public function addShape(string $name, mixed $value = null): self
    {
        $new = clone $this;
        $new->shape[$name] = $value;

        return $new;
    }

    public function getShapeValue(string $name, mixed $default = null): mixed
    {
        if ($this->hasShapeValue($name)) {
            return $this->shape[$name];
        }

        return $default;
    }

    public function hasShapeValue(string $name): bool
    {
        return array_key_exists($name, $this->shape);
    }
}
