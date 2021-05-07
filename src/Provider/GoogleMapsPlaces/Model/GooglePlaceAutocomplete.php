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
 * Class GooglePlaceAutocomplete.
 *
 * @author gdw96 <gael.de_weerdt@mailoo.org>
 */
final class GooglePlaceAutocomplete extends Address
{
    /**
     * @var string|null
     */
    private $id;
    /**
     * @var string|null
     */
    private $description;
    /**
     * @var int|null
     */
    private $distance_meters;
    /**
     * @var array|null
     */
    private $matchedSubstrings;
    /**
     * @var array|null
     *
     * Represents parts of `description` field (example : 'Paris, France').
     * So, this array should look like:
     * ```
     * [
     *     ['offset' => 0, 'value' => 'Paris'],
     *     ['offset' => 7, 'value' => 'France']
     * ]
     * ```
     */
    private $terms;
    /**
     * @var StructuredFormatting|null
     */
    private $structuredFormatting;
    /**
     * @var array
     */
    private $types;

    /**
     * @return string|null
     *
     * @see https://developers.google.com/places/place-id
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     *
     * @return GooglePlaceAutocomplete
     */
    public function withId(?string $id = null): GooglePlaceAutocomplete
    {
        $new = clone $this;
        $new->id = $id;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return GooglePlaceAutocomplete
     */
    public function withDescription(?string $description = null): GooglePlaceAutocomplete
    {
        $new = clone $this;
        $new->description = $description;

        return $new;
    }

    /**
     * @return int|null
     */
    public function getDistanceMeters(): ?int
    {
        return $this->distance_meters;
    }

    /**
     * @param int|null $distance_meters
     *
     * @return GooglePlaceAutocomplete
     */
    public function withDistanceMeters(?int $distance_meters = null): GooglePlaceAutocomplete
    {
        $new = clone $this;
        $new->distance_meters = $distance_meters;

        return $new;
    }

    /**
     * @return array|null
     */
    public function getMatchedSubstrings(): ?array
    {
        return $this->matchedSubstrings;
    }

    /**
     * @param array|null $matchedSubstrings
     *
     * @return GooglePlaceAutocomplete
     */
    public function withMatchedSubstrings(?array $matchedSubstrings = null): GooglePlaceAutocomplete
    {
        $new = clone $this;
        $new->matchedSubstrings = (count($matchedSubstrings) > 0) ? $matchedSubstrings : null;

        return $new;
    }

    /**
     * @return array|null
     */
    public function getTerms(): ?array
    {
        return $this->terms;
    }

    /**
     * @param array|null $terms
     *
     * @return GooglePlaceAutocomplete
     */
    public function withTerms(?array $terms = null): GooglePlaceAutocomplete
    {
        $new = clone $this;
        $new->terms = $terms;

        return $new;
    }

    /**
     * @return StructuredFormatting|null
     */
    public function getStructuredFormatting(): ?StructuredFormatting
    {
        return $this->structuredFormatting;
    }

    /**
     * @param StructuredFormatting|null $structuredFormatting
     *
     * @return GooglePlaceAutocomplete
     */
    public function withStructuredFormatting(?StructuredFormatting $structuredFormatting = null): GooglePlaceAutocomplete
    {
        $new = clone $this;
        $new->structuredFormatting = $structuredFormatting;

        return $new;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param array $types
     *
     * @return GooglePlaceAutocomplete
     */
    public function withTypes(array $types): GooglePlaceAutocomplete
    {
        $new = clone $this;
        $new->types = $types;

        return $new;
    }
}
