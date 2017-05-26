<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder;

use Geocoder\Exception\CollectionIsEmpty;

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
     *
     * @throws CollectionIsEmpty
     */
    public function first(): Location;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @return Location[]
     */
    public function slice($offset, $length = null);

    /**
     * @return bool
     */
    public function has($index): bool;

    /**
     * @return Location
     *
     * @throws \OutOfBoundsException
     */
    public function get($index): Location;

    /**
     * @return Location[]
     */
    public function all();
}
