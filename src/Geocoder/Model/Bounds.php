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
final class Bounds
{
    private $south;

    private $west;

    private $north;

    private $east;

    /**
     * @param double $south
     * @param double $west
     * @param double $north
     * @param double $east
     */
    public function __construct($south, $west, $north, $east)
    {
        $this->south = $south;
        $this->west  = $west;
        $this->north = $north;
        $this->east  = $east;
    }

    public function getSouth()
    {
        return $this->south;
    }

    public function getWest()
    {
        return $this->west;
    }

    public function getNorth()
    {
        return $this->north;
    }

    public function getEast()
    {
        return $this->east;
    }

    public function isDefined()
    {
        return !empty($this->south) && !empty($this->east) && !empty($this->north) && !empty($this->west);
    }

    public function toArray()
    {
        return [
            'south' => $this->getSouth(),
            'west'  => $this->getWest(),
            'north' => $this->getNorth(),
            'east'  => $this->getEast()
        ];
    }
}
