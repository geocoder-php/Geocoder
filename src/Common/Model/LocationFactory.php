<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Model;

use Geocoder\Location;

/**
 * Create an Address or any other subclass of a Location from an array.
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 * @author William Durand <william.durand1@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * @deprecated Use LocationBuilder
 */
final class LocationFactory
{
    /**
     * @param array  $data
     * @param string $class
     *
     * @return Location
     */
    public static function createLocation(array $data, $class = Address::class)
    {
        if (!is_a($class, Location::class, true)) {
            throw new \LogicException('Second parameter to LocationFactory::build must be a class name implementing Geocoder\Location');
        }

        $adminLevels = [];
        foreach (self::readArrayValue($data, 'adminLevels') as $adminLevel) {
            $adminLevels[] = new AdminLevel(
                intval(self::readStringValue($adminLevel, 'level')),
                self::readStringValue($adminLevel, 'name'),
                self::readStringValue($adminLevel, 'code')
            );
        }

        $address = new $class(
            self::createCoordinates(
                self::readDoubleValue($data, 'latitude'),
                self::readDoubleValue($data, 'longitude')
            ),
            self::createBounds(
                self::readDoubleValue($data, 'bounds.south'),
                self::readDoubleValue($data, 'bounds.west'),
                self::readDoubleValue($data, 'bounds.north'),
                self::readDoubleValue($data, 'bounds.east')
            ),
            self::readStringValue($data, 'streetNumber'),
            self::readStringValue($data, 'streetName'),
            self::readStringValue($data, 'postalCode'),
            self::readStringValue($data, 'locality'),
            self::readStringValue($data, 'subLocality'),
            new AdminLevelCollection($adminLevels),
            new Country(
                self::readStringValue($data, 'country'),
                self::upperize(\igorw\get_in($data, ['countryCode']))
            ),
            \igorw\get_in($data, ['timezone'])
        );

        return $address;
    }

    /**
     * @param array  $data
     * @param string $key
     *
     * @return float
     */
    private static function readDoubleValue(array $data, $key)
    {
        return \igorw\get_in($data, explode('.', $key));
    }

    /**
     * @param array  $data
     * @param string $key
     *
     * @return string
     */
    private static function readStringValue(array $data, $key)
    {
        return self::valueOrNull(\igorw\get_in($data, [$key]));
    }

    /**
     * @param array  $data
     * @param string $key
     *
     * @return array
     */
    private static function readArrayValue(array $data, $key)
    {
        return \igorw\get_in($data, [$key]) ?: [];
    }

    /**
     * @return string|null
     */
    private static function valueOrNull($str)
    {
        return empty($str) ? null : $str;
    }

    /**
     * @return string|null
     */
    private static function upperize($str)
    {
        if (null !== $str = self::valueOrNull($str)) {
            return extension_loaded('mbstring') ? mb_strtoupper($str, 'UTF-8') : strtoupper($str);
        }

        return null;
    }

    /**
     * @param float $latitude
     * @param float $longitude
     *
     * @return Coordinates|null
     */
    private static function createCoordinates($latitude, $longitude)
    {
        if (null === $latitude || null === $longitude) {
            return null;
        }

        return new Coordinates((float) $latitude, (float) $longitude);
    }

    /**
     * @param float $south
     * @param float $west
     * @param float $north
     *
     * @return Bounds|null
     */
    private static function createBounds($south, $west, $north, $east)
    {
        if (null === $south || null === $west || null === $north || null === $east) {
            return null;
        }

        return new Bounds((float) $south, (float) $west, (float) $north, (float) $east);
    }
}
