<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Model\AddressCollection;
use Geocoder\Model\Query\GeocodeQuery;
use Geocoder\Model\Query\ReverseQuery;

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
     * @return AddressCollection
     */
    public function geocodeQuery(GeocodeQuery $query);

    /**
     * @param ReverseQuery $query
     *
     * @return AddressCollection
     */
    public function reverseQuery(ReverseQuery $query);

    /**
     * Returns the provider's name.
     *
     * @return string
     */
    public function getName();
}
