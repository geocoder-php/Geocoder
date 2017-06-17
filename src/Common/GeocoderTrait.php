<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder;

use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

/**
 * A trait that turns a Provider into a Geocoder.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
trait GeocoderTrait
{
    abstract public function geocodeQuery(GeocodeQuery $query): Collection;

    abstract public function reverseQuery(ReverseQuery $query): Collection;

    /**
     * {@inheritdoc}
     */
    public function geocode(string $value): Collection
    {
        return $this->geocodeQuery(GeocodeQuery::create($value));
    }

    /**
     * {@inheritdoc}
     */
    public function reverse(float $latitude, float $longitude): Collection
    {
        return $this->reverseQuery(ReverseQuery::fromCoordinates($latitude, $longitude));
    }
}
