<?php

namespace Geocoder\Model;

use Geocoder\Exception\CollectionIsEmpty;
use Geocoder\Collection;
use Geocoder\Location;

final class AddressCollection implements Collection
{
    /**
     * @var Location[]
     */
    private $addresses;

    /**
     * @param Location[] $addresses
     */
    public function __construct(array $addresses = [])
    {
        $this->addresses = array_values($addresses);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->addresses);
    }

    /**
     * @return Location
     */
    public function first()
    {
        if (empty($this->addresses)) {
            throw new CollectionIsEmpty('The Collection instance is empty.');
        }

        return reset($this->addresses);
    }

    /**
     * @return Location[]
     */
    public function slice($offset, $length = null)
    {
        return array_slice($this->addresses, $offset, $length);
    }

    /**
     * @return bool
     */
    public function has($index)
    {
        return isset($this->addresses[$index]);
    }

    /**
     * @return Location
     * @throws \OutOfBoundsException
     */
    public function get($index)
    {
        if (!isset($this->addresses[$index])) {
            throw new \OutOfBoundsException(sprintf('The index "%s" does not exist in this collection.', $index));
        }

        return $this->addresses[$index];
    }

    /**
     * @return Location[]
     */
    public function all()
    {
        return $this->addresses;
    }
}
