<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GoogleMaps\Model;

use Geocoder\Model\Address;
use Geocoder\Model\AdminLevel;
use Geocoder\Model\AdminLevelCollection;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class GoogleAddress extends Address
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $locationType;

    /**
     * @var array
     */
    private $resultType = [];

    /**
     * @var string|null
     */
    private $formattedAddress;

    /**
     * @var string|null
     */
    private $streetAddress;

    /**
     * @var string|null
     */
    private $intersection;

    /**
     * @var string|null
     */
    private $postalCodeSuffix;

    /**
     * @var string|null
     */
    private $political;

    /**
     * @var string|null
     */
    private $colloquialArea;

    /**
     * @var string|null
     */
    private $ward;

    /**
     * @var string|null
     */
    private $neighborhood;

    /**
     * @var string|null
     */
    private $premise;

    /**
     * @var string|null
     */
    private $subpremise;

    /**
     * @var string|null
     */
    private $naturalFeature;

    /**
     * @var string|null
     */
    private $airport;

    /**
     * @var string|null
     */
    private $park;

    /**
     * @var string|null
     */
    private $pointOfInterest;

    /**
     * @var string|null
     */
    private $establishment;

    /**
     * @var AdminLevelCollection
     */
    private $subLocalityLevels;

    /**
     * @var bool
     */
    private $partialMatch;

    /**
     * @return GoogleAddress
     */
    public function withId(string $id = null)
    {
        $new = clone $this;
        $new->id = $id;

        return $new;
    }

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
     * @return GoogleAddress
     */
    public function withLocationType(string $locationType = null)
    {
        $new = clone $this;
        $new->locationType = $locationType;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getLocationType()
    {
        return $this->locationType;
    }

    public function getResultType(): array
    {
        return $this->resultType;
    }

    /**
     * @return GoogleAddress
     */
    public function withResultType(array $resultType)
    {
        $new = clone $this;
        $new->resultType = $resultType;

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
     * @return GoogleAddress
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
    public function getAirport()
    {
        return $this->airport;
    }

    /**
     * @return GoogleAddress
     */
    public function withAirport(string $airport = null)
    {
        $new = clone $this;
        $new->airport = $airport;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getColloquialArea()
    {
        return $this->colloquialArea;
    }

    /**
     * @return GoogleAddress
     */
    public function withColloquialArea(string $colloquialArea = null)
    {
        $new = clone $this;
        $new->colloquialArea = $colloquialArea;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getIntersection()
    {
        return $this->intersection;
    }

    /**
     * @return GoogleAddress
     */
    public function withIntersection(string $intersection = null)
    {
        $new = clone $this;
        $new->intersection = $intersection;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getPostalCodeSuffix()
    {
        return $this->postalCodeSuffix;
    }

    /**
     * @return GoogleAddress
     */
    public function withPostalCodeSuffix(string $postalCodeSuffix = null)
    {
        $new = clone $this;
        $new->postalCodeSuffix = $postalCodeSuffix;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getNaturalFeature()
    {
        return $this->naturalFeature;
    }

    /**
     * @return GoogleAddress
     */
    public function withNaturalFeature(string $naturalFeature = null)
    {
        $new = clone $this;
        $new->naturalFeature = $naturalFeature;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getNeighborhood()
    {
        return $this->neighborhood;
    }

    /**
     * @return GoogleAddress
     */
    public function withNeighborhood(string $neighborhood = null)
    {
        $new = clone $this;
        $new->neighborhood = $neighborhood;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getPark()
    {
        return $this->park;
    }

    /**
     * @return GoogleAddress
     */
    public function withPark(string $park = null)
    {
        $new = clone $this;
        $new->park = $park;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getPointOfInterest()
    {
        return $this->pointOfInterest;
    }

    /**
     * @return GoogleAddress
     */
    public function withPointOfInterest(string $pointOfInterest = null)
    {
        $new = clone $this;
        $new->pointOfInterest = $pointOfInterest;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getPolitical()
    {
        return $this->political;
    }

    /**
     * @return GoogleAddress
     */
    public function withPolitical(string $political = null)
    {
        $new = clone $this;
        $new->political = $political;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getPremise()
    {
        return $this->premise;
    }

    /**
     * @param string $premise
     *
     * @return GoogleAddress
     */
    public function withPremise(string $premise = null)
    {
        $new = clone $this;
        $new->premise = $premise;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getStreetAddress()
    {
        return $this->streetAddress;
    }

    /**
     * @return GoogleAddress
     */
    public function withStreetAddress(string $streetAddress = null)
    {
        $new = clone $this;
        $new->streetAddress = $streetAddress;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getSubpremise()
    {
        return $this->subpremise;
    }

    /**
     * @return GoogleAddress
     */
    public function withSubpremise(string $subpremise = null)
    {
        $new = clone $this;
        $new->subpremise = $subpremise;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getWard()
    {
        return $this->ward;
    }

    /**
     * @return GoogleAddress
     */
    public function withWard(string $ward = null)
    {
        $new = clone $this;
        $new->ward = $ward;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getEstablishment()
    {
        return $this->establishment;
    }

    /**
     * @return GoogleAddress
     */
    public function withEstablishment(string $establishment = null)
    {
        $new = clone $this;
        $new->establishment = $establishment;

        return $new;
    }

    /**
     * @return AdminLevelCollection
     */
    public function getSubLocalityLevels()
    {
        return $this->subLocalityLevels;
    }

    /**
     * @return $this
     */
    public function withSubLocalityLevels(array $subLocalityLevel)
    {
        $levels = array_filter($subLocalityLevel, function ($level) {
            return !empty($level['level']) && (!empty($level['name']) || !empty($level['code']));
        });

        $levelCount = array_count_values(array_column($levels, 'level'));

        $subLocalityLevels = [];
        foreach ($levelCount as $level => $count) {
            $_levels = array_filter($levels, function ($l) use ($level) {
                return $l['level'] === $level;
            });

            $names = array_filter(array_column($_levels, 'name'), function ($name) { return !empty($name); });
            $codes = array_filter(array_column($_levels, 'code'), function ($code) { return !empty($code); });

            $name = count($names) > 0 ? implode(' / ', $names) : implode(' / ', $codes);
            $code = count($codes) > 0 ? implode(' / ', $codes) : null;

            $subLocalityLevels[] = new AdminLevel($level, $name, $code);
        }

        $new = clone $this;
        $new->subLocalityLevels = new AdminLevelCollection($subLocalityLevels);

        return $new;
    }

    /**
     * @return bool
     */
    public function isPartialMatch()
    {
        return $this->partialMatch;
    }

    /**
     * @return $this
     */
    public function withPartialMatch(bool $partialMatch)
    {
        $new = clone $this;
        $new->partialMatch = $partialMatch;

        return $new;
    }
}
