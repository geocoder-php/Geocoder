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

use Geocoder\Provider\Provider;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface Geocoder extends Provider
{
    /**
     * Version of this package.
     */
    public const MAJOR_VERSION = 4;

    public const VERSION = '4.0';

    /**
     * The default result limit.
     */
    public const DEFAULT_RESULT_LIMIT = 5;

    /**
     * Geocodes a given value.
     *
     * @throws Exception\Exception
     */
    public function geocode(string $value): Collection;

    /**
     * Reverses geocode given latitude and longitude values.
     *
     * @throws Exception\Exception
     */
    public function reverse(float $latitude, float $longitude): Collection;
}
