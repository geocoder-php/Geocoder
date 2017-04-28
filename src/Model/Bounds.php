<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Model;

use Geocoder\Assert;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class Bounds
{
    /**
     * @var float
     */
    private $south;

    /**
     * @var float
     */
    private $west;

    /**
     * @var float
     */
    private $north;

    /**
     * @var float
     */
    private $east;

    /**
     * @param float $south
     * @param float $west
     * @param float $north
     * @param float $east
     */
    public function __construct($south, $west, $north, $east)
    {
        $south = (float) $south;
        $north = (float) $north;
        $west = (float) $west;
        $east = (float) $east;

        Assert::latitude($south);
        Assert::latitude($north);
        Assert::longitude($west);
        Assert::longitude($east);

        $this->south = $south;
        $this->west = $west;
        $this->north = $north;
        $this->east = $east;
    }

    /**
     * Returns the south bound.
     *
     * @return float
     */
    public function getSouth()
    {
        return $this->south;
    }

    /**
     * Returns the west bound.
     *
     * @return float
     */
    public function getWest()
    {
        return $this->west;
    }

    /**
     * Returns the north bound.
     *
     * @return float
     */
    public function getNorth()
    {
        return $this->north;
    }

    /**
     * Returns the east bound.
     *
     * @return float
     */
    public function getEast()
    {
        return $this->east;
    }

    /**
     * Returns an array with bounds.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'south' => $this->getSouth(),
            'west' => $this->getWest(),
            'north' => $this->getNorth(),
            'east' => $this->getEast(),
        ];
    }
}
