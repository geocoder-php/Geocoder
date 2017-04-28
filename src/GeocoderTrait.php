<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder;

use Geocoder\Model\Query\GeocodeQuery;
use Geocoder\Model\Query\ReverseQuery;

/**
 * A trait that turns a Provider into a Geocoder.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
trait GeocoderTrait
{
    abstract public function geocodeQuery(GeocodeQuery $query);

    abstract public function reverseQuery(ReverseQuery $query);

    /**
     * {@inheritdoc}
     */
    public function geocode($value)
    {
        return $this->geocodeQuery(GeocodeQuery::create($value));
    }

    /**
     * {@inheritdoc}
     */
    public function reverse($latitude, $longitude)
    {
        return $this->reverseQuery(ReverseQuery::fromCoordinates($latitude, $longitude));
    }
}
