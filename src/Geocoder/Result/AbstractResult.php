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
        if (function_exists('mb_convert_case')) {
            $str = mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
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
        return function_exists('mb_strtolower') ? mb_strtolower($str, 'UTF-8') : strtolower($str);
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
        return function_exists('mb_strtoupper') ? mb_strtoupper($str, 'UTF-8') : strtoupper($str);
    }
}
