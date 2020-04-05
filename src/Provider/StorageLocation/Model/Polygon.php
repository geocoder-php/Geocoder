<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\StorageLocation\Model;

use Geocoder\Model\Coordinates;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
class Polygon
{
    /**
     * @var Coordinates[]
     */
    private $coordinates = [];

    public function __construct(array $coordinates = [])
    {
        $this->coordinates = $coordinates;
    }

    /**
     * @return Coordinates[]
     */
    public function getCoordinates(): array
    {
        return $this->coordinates;
    }

    /**
     * @param Coordinates[] $coordinates
     *
     * @return Polygon
     */
    public function setCoordinates(array $coordinates): self
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    /**
     * @param Coordinates $coordinates
     *
     * @return $this
     */
    public function addCoordinates(Coordinates $coordinates): self
    {
        $this->coordinates[] = $coordinates;

        return $this;
    }

    /**
     * Produce Polygon entity to array
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->coordinates as $key => $coordinate) {
            $result[$key] = $coordinate->toArray();
        }

        return $result;
    }
}
