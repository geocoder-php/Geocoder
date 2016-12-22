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
final class AdminLevel
{
    /**
     * @var int
     */
    private $level;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $code;

    /**
     * @param int    $level
     * @param string $name
     * @param string $code
     */
    public function __construct($level, $name, $code)
    {
        $this->level = $level;
        $this->name = $name;
        $this->code = $code;
    }

    /**
     * Returns the administrative level
     *
     * @return int Level number [1,5]
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Returns the administrative level name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the administrative level short name.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns a string with the administrative level name.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
