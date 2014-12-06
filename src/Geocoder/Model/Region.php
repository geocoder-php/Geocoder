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
 * @author William Durand <william.durand1@gmail.com>
 */
final class Region
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $code;

    /**
     * @param string $name
     * @param string $code
     */
    public function __construct($name, $code)
    {
        $this->name = $name;
        $this->code = $code;
    }

    /**
     * Returns the region name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the region short name.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns a string with the region name.
     *
     * @return string
     */
    public function toString()
    {
        return $this->getName();
    }
}
