<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Yandex\Model;

use Geocoder\Model\Address;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class YandexAddress extends Address
{
    /**
     * @var string|null
     */
    private $precision;

    /**
     * The name of this location.
     *
     * @var string|null
     */
    private $name;

    /**
     * The kind of this location.
     *
     * @var string|null
     */
    private $kind;

    /**
     * @return string|null
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    public function withPrecision(?string $precision = null): self
    {
        $new = clone $this;
        $new->precision = $precision;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    public function withName(?string $name = null): self
    {
        $new = clone $this;
        $new->name = $name;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getKind()
    {
        return $this->kind;
    }

    public function withKind(?string $kind = null): self
    {
        $new = clone $this;
        $new->kind = $kind;

        return $new;
    }
}
