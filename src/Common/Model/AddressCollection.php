<?php

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
use Geocoder\Location;

class AddressCollection implements Collection
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
    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->locations);
    }

    /**
     * {@inheritdoc}
     */
    public function first()
    {
        if (empty($this->locations)) {
            throw new CollectionIsEmpty();
        }

        return reset($this->locations);
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->locations);
    }

    /**
     * @return Location[]
     */
    public function slice($offset, $length = null)
    {
        return array_slice($this->locations, $offset, $length);
    }

    /**
     * @return bool
     */
    public function has($index)
    {
        return isset($this->locations[$index]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($index)
    {
        if (!isset($this->locations[$index])) {
            throw new \OutOfBoundsException(sprintf('The index "%s" does not exist in this collection.', $index));
        }

        return $this->locations[$index];
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->locations;
    }
}
