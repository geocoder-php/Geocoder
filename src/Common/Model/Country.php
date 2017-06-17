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
final class Country
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $code;

    /**
     * @param string $name
     * @param string $code
     */
    public function __construct(string $name = null, string $code = null)
    {
        $this->name = $name;
        $this->code = $code;
    }

    /**
     * Returns the country name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the country ISO code.
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns a string with the country name.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() ?: '';
    }
}
