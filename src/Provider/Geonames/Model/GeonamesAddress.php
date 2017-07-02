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

use Geocoder\Model\Address;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class GeonamesAddress extends Address
{
    /**
     * @var string|null
     */
    private $fcode;

    /**
     * @var string|null
     */
    private $fclName;

    /**
     * @var string|null
     */
    private $fipsCode;

    /**
     * @var string|null
     */
    private $capital;

    /**
     * @var string|null
     */
    private $continent;

    /**
     * @var array
     */
    private $languages = [];

    /**
     * @var int|null
     */
    private $isoNumeric;

    /**
     * @var string|null
     */
    private $isoAlpha3;

    /**
     * @var float|null
     */
    private $areaInSqKm;

    /**
     * @var string|null
     */
    private $currencyCode;

    /**
     * @var int|null
     */
    private $population;

    /**
     * @var int|null
     */
    private $geonameId;

    /**
     * The name of this place.
     *
     * @var string|null
     */
    private $name;

    /**
     * @var array
     */
    private $alternateNames = [];

    /**
     * The name of this location.
     *
     * @var string|null
     */
    private $asciiName;

    /**
     * @return null|string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     *
     * @return self
     */
    public function withName(string $name = null): self
    {
        $new = clone $this;
        $new->name = $name;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getFcode()
    {
        return $this->fcode;
    }

    /**
     * @param null|string $fcode
     *
     * @return GeonamesAddress
     */
    public function withFcode($fcode)
    {
        $new = clone $this;
        $new->fcode = $fcode;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getFclName()
    {
        return $this->fclName;
    }

    /**
     * @param null|string $fclName
     *
     * @return GeonamesAddress
     */
    public function withFclName($fclName)
    {
        $new = clone $this;
        $new->fclName = $fclName;

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
     * @return GeonamesAddress
     */
    public function withFipsCode($fipsCode)
    {
        $new = clone $this;
        $new->fipsCode = $fipsCode;

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
     * @return GeonamesAddress
     */
    public function withCapital($capital)
    {
        $new = clone $this;
        $new->capital = $capital;

        return $new;
    }

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
     * @return GeonamesAddress
     */
    public function withContinent($continent)
    {
        $new = clone $this;
        $new->continent = $continent;

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
     * @return GeonamesAddress
     */
    public function withLanguages($languages)
    {
        $new = clone $this;
        $new->languages = explode(',', $languages);

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
     * @return GeonamesAddress
     */
    public function withIsoNumeric($isoNumeric)
    {
        $new = clone $this;
        $new->isoNumeric = $isoNumeric;

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
     * @return GeonamesAddress
     */
    public function withIsoAlpha3($isoAlpha3)
    {
        $new = clone $this;
        $new->isoAlpha3 = $isoAlpha3;

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
     * @return GeonamesAddress
     */
    public function withAreaInSqKm($areaInSqKm)
    {
        $new = clone $this;
        $new->areaInSqKm = $areaInSqKm;

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
     * @return GeonamesAddress
     */
    public function withCurrencyCode($currencyCode)
    {
        $new = clone $this;
        $new->currencyCode = $currencyCode;

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
     * @return GeonamesAddress
     */
    public function withPopulation($population)
    {
        $new = clone $this;
        $new->population = $population;

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
     * @return GeonamesAddress
     */
    public function withGeonameId($geonameId)
    {
        $new = clone $this;
        $new->geonameId = $geonameId;

        return $new;
    }

    /**
     * @return array
     */
    public function getAlternateNames(): array
    {
        return $this->alternateNames;
    }

    /**
     * @param array $alternateNames
     *
     * @return GeonamesAddress
     */
    public function withAlternateNames(array $alternateNames)
    {
        $new = clone $this;
        $new->alternateNames = $alternateNames;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getAsciiName()
    {
        return $this->asciiName;
    }

    /**
     * @param null|string $asciiName
     *
     * @return GeonamesAddress
     */
    public function withAsciiName($asciiName)
    {
        $new = clone $this;
        $new->asciiName = $asciiName;

        return $new;
    }
}
