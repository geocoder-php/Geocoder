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
     * @var string[]
     */
    private $type = [];

    /**
     * @var string|null
     */
    private $formattedAddress;

    /**
     * @var string|null
     */
    private $vicinity;

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
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return GooglePlace
     */
    public function withId(string $id = null)
    {
        $new = clone $this;
        $new->id = $id;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return GooglePlace
     */
    public function withName(string $name = null)
    {
        $new = clone $this;
        $new->name = $name;

        return $new;
    }

    /**
     * @return string[]
     */
    public function getType(): array
    {
        return $this->type;
    }

    /**
     * @param string[] $type
     *
     * @return GooglePlace
     */
    public function withType(array $type)
    {
        $new = clone $this;
        $new->type = $type;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getFormattedAddress()
    {
        return $this->formattedAddress;
    }

    /**
     * @return GooglePlace
     */
    public function withFormattedAddress(string $formattedAddress = null)
    {
        $new = clone $this;
        $new->formattedAddress = $formattedAddress;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getVicinity()
    {
        return $this->vicinity;
    }

    /**
     * @return GooglePlace
     */
    public function withVicinity(string $vicinity = null)
    {
        $new = clone $this;
        $new->vicinity = $vicinity;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return GooglePlace
     */
    public function withIcon(string $icon = null)
    {
        $new = clone $this;
        $new->icon = $icon;

        return $new;
    }

    public function getPlusCode(): ?PlusCode
    {
        return $this->plusCode;
    }

    /**
     * @return GooglePlace
     */
    public function withPlusCode(PlusCode $plusCode = null)
    {
        $new = clone $this;
        $new->plusCode = $plusCode;

        return $new;
    }

    /**
     * @return Photo[]|null
     */
    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * @param Photo[]|null $photos
     *
     * @return GooglePlace
     */
    public function withPhotos(array $photos = null)
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
    public function getPriceLevel()
    {
        return $this->priceLevel;
    }

    /**
     * @return GooglePlace
     */
    public function withPriceLevel(int $priceLevel = null)
    {
        $new = clone $this;
        $new->priceLevel = $priceLevel;

        return $new;
    }

    /**
     * @return float|null
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @return GooglePlace
     */
    public function withRating(float $rating = null)
    {
        $new = clone $this;
        $new->rating = $rating;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getFormattedPhoneNumber()
    {
        return $this->formattedPhoneNumber;
    }

    /**
     * @return GooglePlace
     */
    public function withFormattedPhoneNumber(string $phone)
    {
        $new = clone $this;
        $new->formattedPhoneNumber = $phone;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getInternationalPhoneNumber()
    {
        return $this->internationalPhoneNumber;
    }

    /**
     * @return GooglePlace
     */
    public function withInternationalPhoneNumber(string $phone)
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
     * @return GooglePlace
     */
    public function withWebsite(string $website)
    {
        $new = clone $this;
        $new->website = $website;

        return $new;
    }

    /**
     * @return OpeningHours
     */
    public function getOpeningHours()
    {
        return $this->openingHours;
    }

    /**
     * @return GooglePlace
     */
    public function withOpeningHours(OpeningHours $openingHours)
    {
        $new = clone $this;
        $new->openingHours = $openingHours;

        return $new;
    }

    public function isPermanentlyClosed(): bool
    {
        return $this->permanentlyClosed;
    }

    /**
     * @return GooglePlace
     */
    public function setPermanentlyClosed()
    {
        $new = clone $this;
        $new->permanentlyClosed = true;

        return $new;
    }
}
