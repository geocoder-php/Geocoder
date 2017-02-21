<?php

namespace Geocoder\Model;

use Geocoder\Exception\InvalidArgument;

/**
 * @author Alexander Janssen <a.janssen@tnajanssen.nl>
 */
final class SubLocalityLevelCollection implements \IteratorAggregate, \Countable
{
    const MAX_LEVEL_DEPTH = 5;

    /**
     * @var SubLocalityLevel[]
     */
    private $sublocalityLevels;

    public function __construct(array $sublocalityLevels = [])
    {
        $this->sublocalityLevels = [];

        foreach ($sublocalityLevels as $adminLevel) {
            $level = $adminLevel->getLevel();

            $this->checkLevel($level);

            if ($this->has($level)) {
                throw new InvalidArgument(sprintf("Sublocality level %d is defined twice", $level));
            }

            $this->sublocalityLevels[$level] = $adminLevel;
        }

        ksort($this->sublocalityLevels, SORT_NUMERIC);
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
        return count($this->sublocalityLevels);
    }

    /**
     * @return SubLocalityLevel|null
     */
    public function first()
    {
        if (empty($this->sublocalityLevels)) {
            return null;
        }

        return reset($this->sublocalityLevels);
    }

    /**
     * @return SubLocalityLevel[]
     */
    public function slice($offset, $length = null)
    {
        return array_slice($this->sublocalityLevels, $offset, $length, true);
    }

    /**
     * @return bool
     */
    public function has($level)
    {
        return isset($this->sublocalityLevels[$level]);
    }

    /**
     * @return SubLocalityLevel
     * @throws \OutOfBoundsException
     * @throws InvalidArgument
     */
    public function get($level)
    {
        $this->checkLevel($level);

        if (! isset($this->sublocalityLevels[$level])) {
            throw new InvalidArgument(sprintf("Sublocality level %d is not set for this address", $level));
        }

        return  $this->sublocalityLevels[$level];
    }

    /**
     * @return SubLocalityLevel[]
     */
    public function all()
    {
        return $this->sublocalityLevels;
    }

    /**
     * @param  integer               $level
     * @throws \OutOfBoundsException
     */
    private function checkLevel($level)
    {
        if ($level <= 0 || $level > self::MAX_LEVEL_DEPTH) {
            throw new \OutOfBoundsException(sprintf(
                "Sublocality level should be an integer in [1,%d], %d given",
                self::MAX_LEVEL_DEPTH,
                $level
            ));
        }
    }
}
