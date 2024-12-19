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

use Geocoder\Exception\InvalidArgument;

/**
 * A Country has either a name or a code. A Country will never be without data.
 *
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

    public function __construct(?string $name = null, ?string $code = null)
    {
        if (null === $name && null === $code) {
            throw new InvalidArgument('A country must have either a name or a code');
        }

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
     */
    public function __toString(): string
    {
        return $this->getName() ?: '';
    }
}
