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
     * @return string|null
     */
    public function getContinent()
    {
        return $this->continent;
    }

    /**
     * @return CountryInfo
     */
    public function withContinent(string $continent = null): self
    {
        $new = clone $this;
        $new->continent = $continent;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getCapital()
    {
        return $this->capital;
    }

    /**
     * @return CountryInfo
     */
    public function withCapital(string $capital = null): self
    {
        $new = clone $this;
        $new->capital = $capital;

        return $new;
    }

    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
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
     * @return CountryInfo
     */
    public function withGeonameId(int $geonameId = null): self
    {
        $new = clone $this;
        $new->geonameId = null === $geonameId ? null : (int) $geonameId;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getIsoAlpha3()
    {
        return $this->isoAlpha3;
    }

    /**
     * @return CountryInfo
     */
    public function withIsoAlpha3(string $isoAlpha3 = null): self
    {
        $new = clone $this;
        $new->isoAlpha3 = $isoAlpha3;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getFipsCode()
    {
        return $this->fipsCode;
    }

    /**
     * @return CountryInfo
     */
    public function withFipsCode(string $fipsCode = null): self
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
     * @return int|null
     */
    public function getIsoNumeric()
    {
        return $this->isoNumeric;
    }

    /**
     * @return CountryInfo
     */
    public function withIsoNumeric(string $isoNumeric = null): self
    {
        $new = clone $this;
        $new->isoNumeric = null === $isoNumeric ? null : (int) $isoNumeric;

        return $new;
    }

    /**
     * @return float|null
     */
    public function getAreaInSqKm()
    {
        return $this->areaInSqKm;
    }

    /**
     * @return CountryInfo
     */
    public function withAreaInSqKm(string $areaInSqKm = null): self
    {
        $new = clone $this;
        $new->areaInSqKm = null === $areaInSqKm ? null : (float) $areaInSqKm;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @return CountryInfo
     */
    public function withCountryCode(string $countryCode = null): self
    {
        $new = clone $this;
        $new->countryCode = $countryCode;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getCountryName()
    {
        return $this->countryName;
    }

    /**
     * @return CountryInfo
     */
    public function withCountryName(string $countryName = null): self
    {
        $new = clone $this;
        $new->countryName = $countryName;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getContinentName()
    {
        return $this->continentName;
    }

    /**
     * @return CountryInfo
     */
    public function withContinentName(string $continentName = null): self
    {
        $new = clone $this;
        $new->continentName = $continentName;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @return CountryInfo
     */
    public function withCurrencyCode(string $currencyCode = null): self
    {
        $new = clone $this;
        $new->currencyCode = $currencyCode;

        return $new;
    }
}
