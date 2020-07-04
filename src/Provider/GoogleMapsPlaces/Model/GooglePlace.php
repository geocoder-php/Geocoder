<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GoogleMapsPlaces\Model;

use Geocoder\Model\Address;

/**
 * @author atymic <atymicq@gmail.com>
 */
final class GooglePlace extends Address
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var array
     */
    private $type = [];

    /**
     * @var string|null
     */
    private $formattedAddress;

    /**
     * @var string|null
     */
    private $icon;

    /**
     * @var PlusCode|null
     */
    private $plusCode;

    /**
     * @var Photo[]|null
     */
    private $photos;

    /**
     * @var int|null
     */
    private $priceLevel;

    /**
     * @var float|null
     */
    private $rating;

    /**
     * @var string|null
     */
    private $formattedPhoneNumber;

    /**
     * @var string|null
     */
    private $internationalPhoneNumber;

    /**
     * @var string|null
     */
    private $website;

    /**
     * @var OpeningHours
     */
    private $openingHours;

    /**
     * @var bool
     */
    private $permanentlyClosed = false;

    /**
     * @see https://developers.google.com/places/place-id
     *
     * @return null|string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param null|string $id
     *
     * @return GooglePlace
     */
    public function withId(?string $id = null): self
    {
        $new = clone $this;
        $new->id = $id;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     *
     * @return GooglePlace
     */
    public function withName(?string $name = null): self
    {
        $new = clone $this;
        $new->name = $name;

        return $new;
    }

    /**
     * @return array
     */
    public function getType(): array
    {
        return $this->type;
    }

    /**
     * @param array $type
     *
     * @return GooglePlace
     */
    public function withType(array $type): self
    {
        $new = clone $this;
        $new->type = $type;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getFormattedAddress(): ?string
    {
        return $this->formattedAddress;
    }

    /**
     * @param string|null $formattedAddress
     *
     * @return GooglePlace
     */
    public function withFormattedAddress(?string $formattedAddress = null): self
    {
        $new = clone $this;
        $new->formattedAddress = $formattedAddress;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string|null $icon
     *
     * @return GooglePlace
     */
    public function withIcon(string $icon = null): self
    {
        $new = clone $this;
        $new->icon = $icon;

        return $new;
    }

    /**
     * @return PlusCode|null
     */
    public function getPlusCode(): ?PlusCode
    {
        return $this->plusCode;
    }

    /**
     * @param PlusCode|null $plusCode
     *
     * @return GooglePlace
     */
    public function withPlusCode(?PlusCode $plusCode = null): self
    {
        $new = clone $this;
        $new->plusCode = $plusCode;

        return $new;
    }

    /**
     * @return Photo[]|null
     */
    public function getPhotos(): ?array
    {
        return $this->photos;
    }

    /**
     * @param Photo[]|null $photos
     *
     * @return GooglePlace
     */
    public function withPhotos(?array $photos = null): self
    {
        $new = clone $this;
        $new->photos = $photos;

        return $new;
    }

    /**
     * @see https://developers.google.com/places/web-service/search#find-place-responses
     *
     * 0 — Free
     * 1 — Inexpensive
     * 2 — Moderate
     * 3 — Expensive
     * 4 — Very Expensive
     *
     * @return int|null
     */
    public function getPriceLevel(): ?int
    {
        return $this->priceLevel;
    }

    /**
     * @param int|null $priceLevel
     *
     * @return GooglePlace
     */
    public function withPriceLevel(?int $priceLevel = null): self
    {
        $new = clone $this;
        $new->priceLevel = $priceLevel;

        return $new;
    }

    /**
     * @return float|null
     */
    public function getRating(): ?float
    {
        return $this->rating;
    }

    /**
     * @param float|null $rating
     *
     * @return GooglePlace
     */
    public function withRating(?float $rating = null): self
    {
        $new = clone $this;
        $new->rating = $rating;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getFormattedPhoneNumber(): ?string
    {
        return $this->formattedPhoneNumber;
    }

    /**
     * @param string|null $phone
     *
     * @return GooglePlace
     */
    public function withFormattedPhoneNumber(?string $phone = null): self
    {
        $new = clone $this;
        $new->formattedPhoneNumber = $phone;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getInternationalPhoneNumber(): ?string
    {
        return $this->internationalPhoneNumber;
    }

    /**
     * @param string|null $phone
     *
     * @return GooglePlace
     */
    public function withInternationalPhoneNumber(?string $phone = null): self
    {
        $new = clone $this;
        $new->internationalPhoneNumber = $phone;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param string|null $website
     *
     * @return GooglePlace
     */
    public function withWebsite(?string $website = null): self
    {
        $new = clone $this;
        $new->website = $website;

        return $new;
    }

    /**
     * @return OpeningHours|null
     */
    public function getOpeningHours(): ?OpeningHours
    {
        return $this->openingHours;
    }

    /**
     * @param OpeningHours $openingHours
     *
     * @return GooglePlace
     */
    public function withOpeningHours(OpeningHours $openingHours): self
    {
        $new = clone $this;
        $new->openingHours = $openingHours;

        return $new;
    }

    /**
     * @return bool
     */
    public function isPermanentlyClosed(): bool
    {
        return $this->permanentlyClosed;
    }

    /**
     * @return GooglePlace
     */
    public function setPermanentlyClosed(): self
    {
        $new = clone $this;
        $new->permanentlyClosed = true;

        return $new;
    }
}
