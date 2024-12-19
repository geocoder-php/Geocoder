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
use Geocoder\Exception\OutOfBounds;

/**
 * This is the interface that is always return from a Geocoder.
 *
 * @author William Durand <william.durand1@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * @template-extends \IteratorAggregate<int, Location>
 */
interface Collection extends \IteratorAggregate, \Countable
{
    /**
     * @throws CollectionIsEmpty
     */
    public function first(): Location;

    public function isEmpty(): bool;

    /**
     * @return Location[]
     */
    public function slice(int $offset, ?int $length = null);

    public function has(int $index): bool;

    /**
     * @throws OutOfBounds
     */
    public function get(int $index): Location;

    /**
     * @return Location[]
     */
    public function all(): array;
}
