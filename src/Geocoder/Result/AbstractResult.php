<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Result;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
abstract class AbstractResult implements \ArrayAccess
{
    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return property_exists($this, $offset) && null !== $this->$offset;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->$offset : null;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        if ($this->offsetExists($offset)) {
            $this->$offset = $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->$offset = null;
        }
    }

    /**
     * Format a string data.
     *
     * @param string $str A string.
     *
     * @return string
     */
    protected function formatString($str)
    {
        if (extension_loaded('mbstring')) {
            $originalStr = $str;
            $str = mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');

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

        $str = str_replace('-', '- ', $str);
        $str = str_replace('- ', '-', $str);

        return $str;
    }

    /**
     * Make a string lowercase.
     *
     * @param string $str A string.
     *
     * @return string
     */
    protected function lowerize($str)
    {
        return extension_loaded('mbstring') ? mb_strtolower($str, 'UTF-8') : strtolower($str);
    }

    /**
     * Make a string uppercase.
     *
     * @param string $str A string.
     *
     * @return string
     */
    protected function upperize($str)
    {
        return extension_loaded('mbstring') ? mb_strtoupper($str, 'UTF-8') : strtoupper($str);
    }
}
