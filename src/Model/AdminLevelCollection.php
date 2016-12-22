<?php

namespace Geocoder\Model;

use Geocoder\Exception\InvalidArgument;

/**
 * @author Giorgio Premi <giosh94mhz@gmail.com>
 */
final class AdminLevelCollection implements  \IteratorAggregate, \Countable
{
    const MAX_LEVEL_DEPTH = 5;

    /**
     * @var AdminLevel[]
     */
    private $adminLevels;

    /**
     *
     * @param AdminLevel[] $adminLevels
     */
    public function __construct(array $adminLevels = [])
    {
        $this->adminLevels = [];

        foreach ($adminLevels as $adminLevel) {
            $level = $adminLevel->getLevel();

            $this->checkLevel($level);

            if ($this->has($level)) {
                 throw new InvalidArgument(sprintf("Administrative level %d is defined twice", $level));
            }

            $this->adminLevels[$level] = $adminLevel;
        }

        ksort($this->adminLevels, SORT_NUMERIC);
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
        return count($this->adminLevels);
    }

    /**
     * @return AdminLevel|null
     */
    public function first()
    {
        if (empty($this->adminLevels)) {
            return null;
        }

        return reset($this->adminLevels);
    }

    /**
     * @return AdminLevel[]
     */
    public function slice($offset, $length = null)
    {
        return array_slice($this->adminLevels, $offset, $length, true);
    }

    /**
     * @return bool
     */
    public function has($level)
    {
        return isset($this->adminLevels[$level]);
    }

    /**
     * @return AdminLevel
     * @throws \OutOfBoundsException
     * @throws InvalidArgument
     */
    public function get($level)
    {
        $this->checkLevel($level);

        if (! isset($this->adminLevels[$level])) {
            throw new InvalidArgument(sprintf("Administrative level %d is not set for this address", $level));
        }

        return  $this->adminLevels[$level];
    }

    /**
     * @return AdminLevel[]
     */
    public function all()
    {
        return $this->adminLevels;
    }

    /**
     * @param  integer               $level
     * @throws \OutOfBoundsException
     */
    private function checkLevel($level)
    {
        if ($level <= 0 || $level > self::MAX_LEVEL_DEPTH) {
            throw new \OutOfBoundsException(sprintf(
                "Administrative level should be an integer in [1,%d], %d given",
                self::MAX_LEVEL_DEPTH,
                $level
            ));
        }
    }
}
