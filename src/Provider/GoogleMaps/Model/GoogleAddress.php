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

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class GoogleAddress extends Address
{
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
     * @param null|string $locationType
     *
     * @return GoogleAddress
     */
    public function withLocationType(string $locationType = null)
    {
        $new = clone $this;
        $new->locationType = $locationType;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getLocationType()
    {
        return $this->locationType;
    }

    /**
     * @return array
     */
    public function getResultType(): array
    {
        return $this->resultType;
    }

    /**
     * @param array $resultType
     *
     * @return GoogleAddress
     */
    public function withResultType(array $resultType)
    {
        $new = clone $this;
        $new->resultType = $resultType;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getFormattedAddress()
    {
        return $this->formattedAddress;
    }

    /**
     * @param null|string $formattedAddress
     */
    public function withFormattedAddress(string $formattedAddress = null)
    {
        $new = clone $this;
        $new->formattedAddress = $formattedAddress;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getAirport()
    {
        return $this->airport;
    }

    /**
     * @param null|string $airport
     */
    public function withAirport(string $airport = null)
    {
        $new = clone $this;
        $new->airport = $airport;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getColloquialArea()
    {
        return $this->colloquialArea;
    }

    /**
     * @param null|string $colloquialArea
     */
    public function withColloquialArea(string $colloquialArea = null)
    {
        $new = clone $this;
        $new->colloquialArea = $colloquialArea;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getIntersection()
    {
        return $this->intersection;
    }

    /**
     * @param null|string $intersection
     */
    public function withIntersection(string $intersection = null)
    {
        $new = clone $this;
        $new->intersection = $intersection;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getNaturalFeature()
    {
        return $this->naturalFeature;
    }

    /**
     * @param null|string $naturalFeature
     */
    public function withNaturalFeature(string $naturalFeature = null)
    {
        $new = clone $this;
        $new->naturalFeature = $naturalFeature;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getNeighborhood()
    {
        return $this->neighborhood;
    }

    /**
     * @param null|string $neighborhood
     */
    public function withNeighborhood(string $neighborhood = null)
    {
        $new = clone $this;
        $new->neighborhood = $neighborhood;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getPark()
    {
        return $this->park;
    }

    /**
     * @param null|string $park
     */
    public function withPark(string $park = null)
    {
        $new = clone $this;
        $new->park = $park;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getPointOfInterest()
    {
        return $this->pointOfInterest;
    }

    /**
     * @param null|string $pointOfInterest
     */
    public function withPointOfInterest(string $pointOfInterest = null)
    {
        $new = clone $this;
        $new->pointOfInterest = $pointOfInterest;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getPolitical()
    {
        return $this->political;
    }

    /**
     * @param null|string $political
     */
    public function withPolitical(string $political = null)
    {
        $new = clone $this;
        $new->political = $political;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getPremise()
    {
        return $this->premise;
    }

    /**
     * @param null|string $premise
     */
    public function withPremise($premise = null)
    {
        $new = clone $this;
        $new->premise = $premise;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getStreetAddress()
    {
        return $this->streetAddress;
    }

    /**
     * @param null|string $streetAddress
     */
    public function withStreetAddress(string $streetAddress = null)
    {
        $new = clone $this;
        $new->streetAddress = $streetAddress;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getSubpremise()
    {
        return $this->subpremise;
    }

    /**
     * @param null|string $subpremise
     */
    public function withSubpremise(string $subpremise = null)
    {
        $new = clone $this;
        $new->subpremise = $subpremise;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getWard()
    {
        return $this->ward;
    }

    /**
     * @param null|string $ward
     */
    public function withWard(string $ward = null)
    {
        $new = clone $this;
        $new->ward = $ward;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getEstablishment()
    {
        return $this->establishment;
    }

    /**
     * @param null|string $ward
     */
    public function withEstablishment(string $establishment = null)
    {
        $new = clone $this;
        $new->establishment = $establishment;

        return $new;
    }
}
