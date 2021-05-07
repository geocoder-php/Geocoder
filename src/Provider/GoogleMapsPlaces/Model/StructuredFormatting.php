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

/**
 * Class StructuredFormatting.
 *
 * @author gdw96 <gael.de_weerdt@mailoo.org>
 *
 * @see GooglePlaceAutocomplete
 */
class StructuredFormatting
{
    /**
     * @var string|null
     */
    private $mainText;
    /**
     * @var string|null
     */
    private $secondaryText;
    /**
     * @var array|null
     *
     * Represents substrings of `mainText` field that match with input search query.
     * It should look like:
     * ```
     * [
     *     [ 'length' => 5, 'offset' => 0],
     * ]
     * ```
     */
    private $mainTextMatchedSubstrings;

    /**
     * StructuredFormatting constructor.
     *
     * @param string|null $mainText
     * @param string|null $secondaryText
     * @param array|null  $mainTextMatchedSubstrings
     */
    public function __construct(?string $mainText, ?string $secondaryText = null, ?array $mainTextMatchedSubstrings = null)
    {
        $this->mainText = $mainText;
        $this->secondaryText = $secondaryText;
        $this->mainTextMatchedSubstrings = $mainTextMatchedSubstrings;
    }

    /**
     * @return string|null
     */
    public function getMainText(): ?string
    {
        return $this->mainText;
    }

    /**
     * @param string|null $mainText
     *
     * @return StructuredFormatting
     */
    public function setMainText(?string $mainText = null): StructuredFormatting
    {
        $this->mainText = $mainText;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSecondaryText(): ?string
    {
        return $this->secondaryText;
    }

    /**
     * @param string|null $secondaryText
     *
     * @return StructuredFormatting
     */
    public function setSecondaryText(?string $secondaryText = null): StructuredFormatting
    {
        $this->secondaryText = $secondaryText;

        return $this;
    }

    /**
     * Return `null` or array like (it represents substrings matches with search query input):
     * ```
     * [
     *     [ 'length' => 5, 'offset' => 0],
     * ]
     * ```.
     *
     * @return array|null
     */
    public function getMainTextMatchedSubstrings(): ?array
    {
        return $this->mainTextMatchedSubstrings;
    }

    /**
     * `$mainTextMatchedSubstrings` must have this format (example):
     * ```
     * [
     *     [ 'length' => 5, 'offset' => 0],
     * ]
     * ```.
     *
     * @param array|null $mainTextMatchedSubstrings
     *
     * @return StructuredFormatting
     */
    public function setMainTextMatchedSubstrings(?array $mainTextMatchedSubstrings = null): StructuredFormatting
    {
        $this->mainTextMatchedSubstrings = $mainTextMatchedSubstrings;

        return $this;
    }
}
