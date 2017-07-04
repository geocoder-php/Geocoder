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

/**
 * @author Srihari Thalla <thallasrihari@gmail.com>
 */
final class CountryInfo
{
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
    private $continentName;

    /**
     * @var string|null
     */
    private $currencyCode;

    /**
     * @return null|string
     */
    public function getContinent()
    {
        return $this->continent;
    }

    /**
     * @param null|string $continent
     *
     * @return CountryInfo
     */
    public function withContinent(string $continent = null)
    {
        $new = clone $this;
        $new->continent = $continent;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getCapital()
    {
        return $this->capital;
    }

    /**
     * @param null|string $capital
     *
     * @return CountryInfo
     */
    public function withCapital(string $capital = null)
    {
        $new = clone $this;
        $new->capital = $capital;

        return $new;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @param string $languages
     *
     * @return CountryInfo
     */
    public function withLanguages(string $languages = '')
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
    public function withGeonameId(string $geonameId = null)
    {
        $new = clone $this;
        $new->geonameId = null === $geonameId ? null : (int) $geonameId;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getIsoAlpha3()
    {
        return $this->isoAlpha3;
    }

    /**
     * @param null|string $isoAlpha3
     *
     * @return CountryInfo
     */
    public function withIsoAlpha3(string $isoAlpha3 = null)
    {
        $new = clone $this;
        $new->isoAlpha3 = $isoAlpha3;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getFipsCode()
    {
        return $this->fipsCode;
    }

    /**
     * @param null|string $fipsCode
     *
     * @return CountryInfo
     */
    public function withFipsCode(string $fipsCode = null)
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
    public function withPopulation(string $population = null)
    {
        $new = clone $this;
        $new->population = null === $population ? null : (int) $population;

        return $new;
    }

    /**
     * @return null|int
     */
    public function getIsoNumeric()
    {
        return $this->isoNumeric;
    }

    /**
     * @param null|int $isoNumeric
     *
     * @return CountryInfo
     */
    public function withIsoNumeric(string $isoNumeric = null)
    {
        $new = clone $this;
        $new->isoNumeric = null === $isoNumeric ? null : (int) $isoNumeric;

        return $new;
    }

    /**
     * @return null|float
     */
    public function getAreaInSqKm()
    {
        return $this->areaInSqKm;
    }

    /**
     * @param null|float $areaInSqKm
     *
     * @return CountryInfo
     */
    public function withAreaInSqKm(float $areaInSqKm = null)
    {
        $new = clone $this;
        $new->areaInSqKm = null === $areaInSqKm ? null : (float) $areaInSqKm;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getContinentName()
    {
        return $this->continentName;
    }

    /**
     * @param null|string $continentName
     *
     * @return CountryInfo
     */
    public function withContinentName(string $continentName = null)
    {
        $new = clone $this;
        $new->continentName = $continentName;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @param null|string $currencyCode
     *
     * @return CountryInfo
     */
    public function withCurrencyCode(string $currencyCode = null)
    {
        $new = clone $this;
        $new->currencyCode = $currencyCode;

        return $new;
    }
}
