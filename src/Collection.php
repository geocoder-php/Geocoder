<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder;

/**
 * This is the interface that is always return from a Geocoder.
 *
 * @author William Durand <william.durand1@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface Collection extends \IteratorAggregate, \Countable
{
    /**
     * @return Location
     */
    public function first();

    /**
     * @return Location[]
     */
    public function slice($offset, $length = null);

    /**
     * @return bool
     */
    public function has($index);

    /**
     * @return Location
     *
     * @throws \OutOfBoundsException
     */
    public function get($index);

    /**
     * @return Location[]
     */
    public function all();
}
