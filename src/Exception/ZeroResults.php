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
 * No results where returned by the server. The place you are looking for does not exist.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ZeroResults extends \RuntimeException implements Exception
{
    public static function create($query)
    {
        return new self(sprintf('The geocoder server returned no results for query "%s". The place you are looking for may not exist.', $query));
    }
}
