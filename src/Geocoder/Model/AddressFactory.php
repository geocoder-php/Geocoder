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
    public function createFromArray(array $results)
    {
        $addresses = [];
        foreach ($results as $result) {
            $addresses[] = new Address(
                new Coordinates(
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
                new County(
                    $this->readStringValue($result, 'county'),
                    $this->upperize(\igorw\get_in($result, ['countyCode']))
                ),
                new Region(
                    $this->readStringValue($result, 'region'),
                    $this->upperize(\igorw\get_in($result, ['regionCode']))
                ),
                new Country(
                    $this->readStringValue($result, 'country'),
                    $this->upperize(\igorw\get_in($result, ['countryCode']))
                ),
                \igorw\get_in($result, ['timezone'])
            );
        }

        return $addresses;
    }

    /**
     * @param string $key
     */
    private function readDoubleValue(array $data, $key)
    {
        return (double) \igorw\get_in($data, explode('.', $key));
    }

    /**
     * @param string $key
     */
    private function readStringValue(array $data, $key)
    {
        return $this->formatString(\igorw\get_in($data, [ $key ]));
    }

    private function formatString($str)
    {
        if (null !== $str) {
            if (extension_loaded('mbstring')) {
                $originalStr = $str;
                $str         = mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
                // Correct for MB_TITLE_CASE's insistence on uppercasing letters
                // immediately preceded by numerals, eg: 1st -> 1St
                $originalEncoding = mb_regex_encoding();
                mb_regex_encoding('UTF-8');
                // matches an upper case letter character immediately preceded by a numeral
                mb_ereg_search_init($str, '[0-9]\p{Lu}');

                while ($match = mb_ereg_search_pos()) {
                    $charPos = $match[0] + 1;
                    // Only swap it back to lowercase if it was lowercase to begin with
                    if (mb_ereg_match('\p{Ll}', $originalStr[$charPos])) {
                        $str[$charPos] = mb_strtolower($str[$charPos]);
                    }
                }

                mb_regex_encoding($originalEncoding);
            } else {
                $str = $this->lowerize($str);
                $str = ucwords($str);
            }

            $str = str_replace('-', '', $str);
            $str = str_replace('', '-', $str);
        }

        return $str;
    }

    private function lowerize($str)
    {
        return extension_loaded('mbstring') ? mb_strtolower($str, 'UTF-8') : strtolower($str);
    }

    private function upperize($str)
    {
        return extension_loaded('mbstring') ? mb_strtoupper($str, 'UTF-8') : strtoupper($str);
    }
}
