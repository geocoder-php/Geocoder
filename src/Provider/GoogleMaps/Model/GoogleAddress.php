<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GoogleMaps\Model;

use Geocoder\Model\Address;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class GoogleAddress extends Address
{
    /**
     * @var string|null
     */
    private $locationType;

    /**
     * @var array
     */
    private $resultType = [];

    /**
     * @var string|null
     */
    private $formattedAddress;

    /**
     * @param null|string $locationType
     *
     * @return GoogleAddress
     */
    public function withLocationType($locationType)
    {
        $new = clone $this;
        $new->locationType = $locationType;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getLocationType()
    {
        return $this->locationType;
    }

    /**
     * @return array
     */
    public function getResultType()
    {
        return $this->resultType;
    }

    /**
     * @param array $resultType
     *
     * @return GoogleAddress
     */
    public function withResultType(array $resultType)
    {
        $new = clone $this;
        $new->resultType = $resultType;

        return $new;
    }

    /**
     * @return null|string
     */
    public function getFormattedAddress()
    {
        return $this->formattedAddress;
    }

    /**
     * @param null|string $formattedAddress
     */
    public function withFormattedAddress($formattedAddress)
    {
        $new = clone $this;
        $new->formattedAddress = $formattedAddress;

        return $new;
    }
}
