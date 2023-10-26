<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Pelias\Model;

use Geocoder\Model\Address;

class PeliasAddress extends Address
{
    /**
     * The pelias layer returned
     * @var string|null
     */
    private ?string $layer = null;

    /**
     * Confidence score from pelias
     * @var float|null
     */
    private ?float $confidence = null;

    /**
     * Match type from pelias
     * @var string|null
     */
    private ?string $matchType = null;

    /**
     * Data source from pelias
     * @var string|null
     */
    private ?string $source = null;

    /**
     * Accuracy from pelias
     * @var string|null
     */
    private ?string $accuracy = null;

    public static function createFromArray(array $data)
    {
        $address = parent::createFromArray($data);
        $address->layer = $data['layer'] ?? null;
        $address->confidence = $data['confidence'] ?? null;
        $address->matchType = $data['match_type'] ?? null;
        $address->source = $data['source'] ?? null;
        $address->accuracy = $data['accuracy'] ?? null;
        return $address;
    }

    /**
     * Get the pelias layer returned
     *
     * @return  string|null
     */ 
    public function getLayer()
    {
        return $this->layer;
    }

    /**
     * Get confidence score from pelias
     *
     * @return  float|null
     */ 
    public function getConfidence()
    {
        return $this->confidence;
    }

    /**
     * Get match type from pelias
     *
     * @return  string|null
     */ 
    public function getMatchType()
    {
        return $this->matchType;
    }

    /**
     * Get data source from pelias
     *
     * @return  string|null
     */ 
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Get accuracy from pelias
     *
     * @return  string|null
     */
    public function getAccuracy()
    {
        return $this->accuracy;
    }

    /**
     * Set the pelias layer returned
     * @param string|null $layer name of the pelias layer
     * @return PeliasAddress
     */
    public function withLayer(string $layer = null)
    {
        $new = clone $this;
        $new->layer = $layer;

        return $new;
    }

    /**
     * Set confidence score from pelias
     * @param float|null $confidence confidence level as a float
     * @return PeliasAddress
     */
    public function withConfidence(float $confidence = null)
    {
        $new = clone $this;
        $new->confidence = $confidence;

        return $new;
    }

    /**
     * Set match type from pelias
     * @param string|null $matchType precision of the match like "exact"
     * @return PeliasAddress
     */
    public function withMatchType(string $matchType = null)
    {
        $new = clone $this;
        $new->matchType = $matchType;

        return $new;
    }

    /**
     * Set data source from pelias
     * @param string|null $source address source from pelias
     * @return PeliasAddress
     */
    public function withSource(string $source = null)
    {
        $new = clone $this;
        $new->source = $source;

        return $new;
    }

    /**
     * Set accuracy from pelias
     * @param string|null $accuracy accuracy level from pelias like "point"
     * @return PeliasAddress
     */
    public function withAccuracy(string $accuracy = null)
    {
        $new = clone $this;
        $new->accuracy = $accuracy;

        return $new;
    }

}