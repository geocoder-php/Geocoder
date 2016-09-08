<?php
namespace Geocoder\Model;

use Geocoder\Exception\InvalidArgument;


/**
 * @author Giorgio Premi <giosh94mhz@gmail.com>
 */
interface AdminLevelCollectionInterface extends \IteratorAggregate, \Countable
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
     * @return AdminLevelInterface|null
     */
    public function first();

    /**
     * @return AdminLevelInterface[]
     */
    public function slice($offset, $length = null);

    /**
     * @return bool
     */
    public function has($level);

    /**
     * @return AdminLevelInterface
     * @throws \OutOfBoundsException
     * @throws InvalidArgument
     */
    public function get($level);

    /**
     * @return AdminLevelInterface[]
     */
    public function all();
}