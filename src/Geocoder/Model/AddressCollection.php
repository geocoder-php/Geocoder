<?php

namespace Geocoder\Model;

use Geocoder\Exception\CollectionIsEmpty;
use Geocoder\GeocoderResult;

final class AddressCollection implements GeocoderResult
{
    /**
     * @var Position[]
     */
    private $addresses;

    /**
     * @param Position[] $addresses
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
     * @return Position
     */
    public function first()
    {
        if (empty($this->addresses)) {
            throw new CollectionIsEmpty('The GeocoderResult instance is empty.');
        }

        return reset($this->addresses);
    }

    /**
     * @return Position[]
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
     * @return Position
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
     * @return Position[]
     */
    public function all()
    {
        return $this->addresses;
    }
}
