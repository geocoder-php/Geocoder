<?php

namespace Geocoder;

use Geocoder\Model\Coordinates;
use Geocoder\Model\Query\GeocodeQuery;
use Geocoder\Model\Query\ReverseQuery;

/**
 * A trait that turns a Provider into a Geocoder
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
trait GeocoderTrait
{
    abstract public function geocodeQuery(GeocodeQuery $query);

    abstract public function reverseQuery(ReverseQuery $query);

    /**
     * {@inheritDoc}
     */
    public function geocode($value)
    {
        return $this->geocodeQuery(GeocodeQuery::create($value));
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        return $this->reverseQuery(ReverseQuery::fromCoordinates($latitude, $longitude));
    }
}