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
     * @var string|null
     */
    private $code;

    /**
     * @param int         $level
     * @param string      $name
     * @param string|null $code
     */
    public function __construct(int $level, string $name, string $code = null)
    {
        $this->level = $level;
        $this->name = $name;
        $this->code = $code;
    }

    /**
     * Returns the administrative level.
     *
     * @return int Level number [1,5]
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Returns the administrative level name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the administrative level short name.
     *
     * @return string|null
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
    public function __toString(): string
    {
        return $this->getName();
    }
}
