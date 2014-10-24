<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\NoResult;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\UnsupportedOperation;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface Provider
{
    /**
     * Returns an associative array with data treated by the provider.
     *
     * @param string $address An address (IP or street).
     *
     * @throws NoResult             If the address could not be resolved
     * @throws InvalidCredentials   If the credentials are invalid
     * @throws UnsupportedOperation If IPv4, IPv6 or street is not supported
     *
     * @return array
     */
    public function getGeocodedData($address);

    /**
     * Returns an associative array with data treated by the provider.
     *
     * @param array $coordinates Coordinates (latitude, longitude).
     *
     * @throws NoResult             If the coordinates could not be resolved
     * @throws InvalidCredentials   If the credentials are invalid
     * @throws UnsupportedOperation If reverse geocoding is not supported
     *
     * @return array
     */
    public function getReversedData(array $coordinates);

    /**
     * Returns the provider's name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the maximum number of returned results.
     *
     * @param integer $maxResults
     *
     * @return Provider
     */
    public function setMaxResults($maxResults);
}
