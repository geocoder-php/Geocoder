<?php

declare(strict_types=1);

/*
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
final class InvalidServerResponse extends \RuntimeException implements Exception
{
    /**
     * @param string $query
     * @param int    $code
     *
     * @return InvalidServerResponse
     */
    public static function create(string $query, int $code = 0): self
    {
        return new self(sprintf('The geocoder server returned an invalid response (%d) for query "%s". We could not parse it.', $code, $query));
    }

    /**
     * @param string $query
     *
     * @return InvalidServerResponse
     */
    public static function emptyResponse(string $query): self
    {
        return new self(sprintf('The geocoder server returned an empty response for query "%s".', $query));
    }
}
