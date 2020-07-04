<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Geonames\Model;

use Geocoder\Exception\InvalidArgument;
use Geocoder\Model\Bounds;

/**
 * @author Srihari Thalla <thallasrihari@gmail.com>
 */
final class CountryInfo
{
    /**
     * @var Bounds|null
     */
    private $bounds;

    /**
     * @var string|null
     */
    private $continent;

    /**
     * @var string|null
     */
    private $capital;

    /**
     * @var array
     */
    private $languages = [];

    /**
     * @var int|null
     */
    private $geonameId;

    /**
     * @var string|null
     */
    private $isoAlpha3;

    /**
     * @var string|null
     */
    private $fipsCode;

    /**
     * @var int|null
     */
    private $population;

    /**
     * @var int|null
     */
    private $isoNumeric;

    /**
     * @var float|null
     */
    private $areaInSqKm;

    /**
     * @var string|null
     */
    private $countryCode;

    /**
     * @var string|null
     */
    private $countryName;

    /**
     * @var string|null
     */
    private $continentName;

    /**
     * @var string|null
     */
    private $currencyCode;

    /**
     * @return Bounds|null
     */
    public function getBounds()
    {
        return $this->bounds;
    }

    /**
     * @param float $south
     * @param float $west
     * @param float $north
     * @param float $east
     *
     * @return CountryInfo
     */
    public function setBounds(float $south, float $west, float $north, float $east): self
    {
        $new = clone $this;

        try {
            $new->bounds = new Bounds($south, $west, $north, $east);
        } catch (InvalidArgument $e) {
            $new->bounds = null;
        }

        return $new;
    }

    /**
     * @return null|string
     */
    public function getContinent(): ?string
    {
        return $this->continent;
    }

    /**
     * @param null|string $continent
     *
     * @return CountryInfo
     */
    public function withContinent(?string $continent = null): self
    {
        $new = clone $this;
        $new->continent = $continent;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getCapital(): ?string
    {
        return $this->capital;
    }

    /**
     * @param null|string $capital
     *
     * @return CountryInfo
     */
    public function withCapital(?string $capital = null): self
    {
        $new = clone $this;
        $new->capital = $capital;

        return $new;
    }

    /**
     * @return array
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * @param string $languages
     *
     * @return CountryInfo
     */
    public function withLanguages(string $languages = ''): self
    {
        $new = clone $this;
        $new->languages = explode(',', $languages);

        return $new;
    }

    /**
     * @return int|null
     */
    public function getGeonameId()
    {
        return $this->geonameId;
    }

    /**
     * @param int|null $geonameId
     *
     * @return CountryInfo
     */
    public function withGeonameId(int $geonameId = null): self
    {
        $new = clone $this;
        $new->geonameId = null === $geonameId ? null : (int) $geonameId;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getIsoAlpha3(): ?string
    {
        return $this->isoAlpha3;
    }

    /**
     * @param null|string $isoAlpha3
     *
     * @return CountryInfo
     */
    public function withIsoAlpha3(?string $isoAlpha3 = null): self
    {
        $new = clone $this;
        $new->isoAlpha3 = $isoAlpha3;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getFipsCode(): ?string
    {
        return $this->fipsCode;
    }

    /**
     * @param null|string $fipsCode
     *
     * @return CountryInfo
     */
    public function withFipsCode(?string $fipsCode = null): self
    {
        $new = clone $this;
        $new->fipsCode = $fipsCode;

        return $new;
    }

    /**
     * @return int|null
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * @param int|null $population
     *
     * @return CountryInfo
     */
    public function withPopulation(string $population = null): self
    {
        $new = clone $this;
        $new->population = null === $population ? null : (int) $population;

        return $new;
    }

    /**
     * @return null|int
     */
    public function getIsoNumeric(): ?int
    {
        return $this->isoNumeric;
    }

    /**
     * @param string|null $isoNumeric
     *
     * @return CountryInfo
     */
    public function withIsoNumeric(?string $isoNumeric = null): self
    {
        $new = clone $this;
        $new->isoNumeric = null === $isoNumeric ? null : (int) $isoNumeric;

        return $new;
    }

    /**
     * @return null|float
     */
    public function getAreaInSqKm(): ?float
    {
        return $this->areaInSqKm;
    }

    /**
     * @param string|null $areaInSqKm
     *
     * @return CountryInfo
     */
    public function withAreaInSqKm(?string $areaInSqKm = null): self
    {
        $new = clone $this;
        $new->areaInSqKm = null === $areaInSqKm ? null : (float) $areaInSqKm;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * @param string|null $countryCode
     *
     * @return CountryInfo
     */
    public function withCountryCode(?string $countryCode = null): self
    {
        $new = clone $this;
        $new->countryCode = $countryCode;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getCountryName(): ?string
    {
        return $this->countryName;
    }

    /**
     * @param string|null $countryName
     *
     * @return CountryInfo
     */
    public function withCountryName(?string $countryName = null): self
    {
        $new = clone $this;
        $new->countryName = $countryName;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getContinentName(): ?string
    {
        return $this->continentName;
    }

    /**
     * @param null|string $continentName
     *
     * @return CountryInfo
     */
    public function withContinentName(?string $continentName = null): self
    {
        $new = clone $this;
        $new->continentName = $continentName;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    /**
     * @param null|string $currencyCode
     *
     * @return CountryInfo
     */
    public function withCurrencyCode(?string $currencyCode = null): self
    {
        $new = clone $this;
        $new->currencyCode = $currencyCode;

        return $new;
    }
}
