<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\NoResult;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Geocoder;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface Provider extends Geocoder
{
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
