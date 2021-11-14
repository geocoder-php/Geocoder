<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Model;

use Geocoder\Collection;
use Geocoder\Exception\CollectionIsEmpty;
use Geocoder\Exception\OutOfBounds;
use Geocoder\Location;
use Traversable;

final class AddressCollection implements Collection
{
    /**
     * @var Location[]
     */
    private $locations;

    /**
     * @param Location[] $locations
     */
    public function __construct(array $locations = [])
    {
        $this->locations = array_values($locations);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->locations);
    }

    /**
     * {@inheritdoc}
     */
    public function first(): Location
    {
        if ([] === $this->locations) {
            throw new CollectionIsEmpty();
        }

        return reset($this->locations);
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty(): bool
    {
        return [] === $this->locations;
    }

    /**
     * @return Location[]
     */
    public function slice(int $offset, int $length = null)
    {
        return array_slice($this->locations, $offset, $length);
    }

    /**
     * @return bool
     */
    public function has(int $index): bool
    {
        return isset($this->locations[$index]);
    }

    /**
     * {@inheritdoc}
     */
    public function get(int $index): Location
    {
        if (!isset($this->locations[$index])) {
            throw new OutOfBounds(sprintf('The index "%s" does not exist in this collection.', $index));
        }

        return $this->locations[$index];
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->locations;
    }
}
