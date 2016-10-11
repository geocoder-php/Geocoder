<?php
namespace Geocoder;

use Geocoder\Model\Position;

/**
 * This is the interface that is always return from a Geocoder.
 *
 * @author William Durand <william.durand1@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface GeocoderResult extends \IteratorAggregate, \Countable
{
    /**
     * {@inheritDoc}
     */
    public function getIterator();

    /**
     * {@inheritDoc}
     */
    public function count();

    /**
     * @return Position
     */
    public function first();

    /**
     * @return Position[]
     */
    public function slice($offset, $length = null);

    /**
     * @return bool
     */
    public function has($index);

    /**
     * @return Position
     * @throws \OutOfBoundsException
     */
    public function get($index);

    /**
     * @return Position[]
     */
    public function all();
}