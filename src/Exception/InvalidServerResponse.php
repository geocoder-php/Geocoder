<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Exception;

/**
 * When the geocoder server returns something that we cannot process.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class InvalidServerResponse extends ZeroResults implements Exception
{
    public static function create($query)
    {
        return new self(sprintf('The geocoder server returned an invalid response for query "%s". We could not parse it.', $query));
    }
}
