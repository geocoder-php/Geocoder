<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GoogleMapsPlaces\Model;

/**
 * @author atymic <atymicq@gmail.com>
 */
class PlusCode
{
    /**
     * @var string
     */
    private $globalCode;

    /**
     * @var string
     */
    private $compoundCode;

    /**
     * @param string $globalCode
     * @param string $compoundCode
     */
    public function __construct(string $globalCode, string $compoundCode)
    {
        $this->globalCode = $globalCode;
        $this->compoundCode = $compoundCode;
    }

    /**
     * @return string
     */
    public function getGlobalCode(): string
    {
        return $this->globalCode;
    }

    /**
     * @return string
     */
    public function getCompoundCode(): string
    {
        return $this->compoundCode;
    }
}
