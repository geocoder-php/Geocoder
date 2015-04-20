<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Model;

/**
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 * @author William Durand <william.durand1@gmail.com>
 */
final class AddressFactory
{
    /**
     * @param  array                             $results
     * @return \Geocoder\Model\AddressCollection
     */
    public function createFromArray(array $results)
    {
        $addresses = [];
        foreach ($results as $result) {
            $adminLevels = [];
            foreach ($this->readArrayValue($result, 'adminLevels') as $adminLevel) {
                $adminLevels[] = new AdminLevel(
                    intval($this->readStringValue($adminLevel, 'level')),
                    $this->readStringValue($adminLevel, 'name'),
                    $this->readStringValue($adminLevel, 'code')
                );
            }

            $addresses[] = new Address(
                $this->createCoordinates(
                    $this->readDoubleValue($result, 'latitude'),
                    $this->readDoubleValue($result, 'longitude')
                ),
                new Bounds(
                    $this->readDoubleValue($result, 'bounds.south'),
                    $this->readDoubleValue($result, 'bounds.west'),
                    $this->readDoubleValue($result, 'bounds.north'),
                    $this->readDoubleValue($result, 'bounds.east')
                ),
                $this->readStringValue($result, 'streetNumber'),
                $this->readStringValue($result, 'streetName'),
                $this->readStringValue($result, 'postalCode'),
                $this->readStringValue($result, 'locality'),
                $this->readStringValue($result, 'subLocality'),
                new AdminLevelCollection($adminLevels),
                new Country(
                    $this->readStringValue($result, 'country'),
                    $this->upperize(\igorw\get_in($result, ['countryCode']))
                ),
                \igorw\get_in($result, ['timezone'])
            );
        }

        return new AddressCollection($addresses);
    }

    /**
     * @param  array  $data
     * @param  string $key
     * @return double
     */
    private function readDoubleValue(array $data, $key)
    {
        return \igorw\get_in($data, explode('.', $key));
    }

    /**
     * @param  array  $data
     * @param  string $key
     * @return string
     */
    private function readStringValue(array $data, $key)
    {
        return $this->valueOrNull(\igorw\get_in($data, [ $key ]));
    }

    /**
     * @param  array  $data
     * @param  string $key
     * @return array
     */
    private function readArrayValue(array $data, $key)
    {
        return \igorw\get_in($data, [ $key ]) ?: [];
    }

    /**
     * @return string|null
     */
    private function valueOrNull($str)
    {
        return empty($str) ? null : $str;
    }

    /**
     * @return string|null
     */
    private function upperize($str)
    {
        if (null !== $str = $this->valueOrNull($str)) {
            return extension_loaded('mbstring') ? mb_strtoupper($str, 'UTF-8') : strtoupper($str);
        }

        return null;
    }

    /**
     * @param double $latitude
     * @param double $longitude
     */
    private function createCoordinates($latitude, $longitude)
    {
        if (null === $latitude || null === $longitude) {
            return null;
        }

        return new Coordinates((double) $latitude, (double) $longitude);
    }
}
