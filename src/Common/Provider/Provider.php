<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Collection;
use Geocoder\Location;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\LookupQuery;
use Geocoder\Query\ReverseQuery;

/**
 * Providers MUST always be stateless and immutable.
 *
 * @author William Durand <william.durand1@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface Provider
{
    /**
     * @param GeocodeQuery $query
     *
     * @return Collection
     *
     * @throws \Geocoder\Exception\Exception
     */
    public function geocodeQuery(GeocodeQuery $query): Collection;

    /**
     * @param ReverseQuery $query
     *
     * @return Collection
     *
     * @throws \Geocoder\Exception\Exception
     */
    public function reverseQuery(ReverseQuery $query): Collection;

    /**
     * Lookup a location by it's provider-specific id.
     *
     * @param LookupQuery $query
     *
     * @return Collection
     *
     * @throws \Geocoder\Exception\Exception
     */
    public function lookupQuery(LookupQuery $query): Collection;

    /**
     * Returns the provider's name.
     *
     * @return string
     */
    public function getName(): string;
}
