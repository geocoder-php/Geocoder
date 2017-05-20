<?php

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
class YandexAddress extends Address
{
    /**
     * @var string|null
     */
    private $precision;

    /**
     * @return null|string
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * @param null|string $precision
     *
     * @return YandexAddress
     */
    public function setPrecision($precision)
    {
        $this->precision = $precision;

        return $this;
    }
}
