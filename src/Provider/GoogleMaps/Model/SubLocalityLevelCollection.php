<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GoogleMaps\Model;

use Geocoder\Exception\CollectionIsEmpty;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\OutOfBounds;

/**
 * Class SubLocalityLevelCollection is used only for GoogleMap provider, contains functions for working with arrays of SubLocalityLevel
 */
final class SubLocalityLevelCollection implements \IteratorAggregate, \Countable
{
    const MAX_LEVEL_DEPTH = 5;

    /**
     * @var SubLocalityLevel[]
     */
    private $subLocalityLevels;

    /**
     * @param SubLocalityLevel[] $subLocalityLevels
     *
     * @throws InvalidArgument
     */
    public function __construct(array $subLocalityLevels = [])
    {
        $this->subLocalityLevels = [];

        foreach ($subLocalityLevels as $subLocalityLevel) {
            $level = $subLocalityLevel->getLevel();

            $this->checkLevel($level);

            if ($this->has($level)) {
                throw new InvalidArgument(sprintf('SubLocality level %d is defined twice', $level));
            }

            $this->subLocalityLevels[$level] = $subLocalityLevel;
        }

        ksort($this->subLocalityLevels, SORT_NUMERIC);
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
        return count($this->subLocalityLevels);
    }

    /**
     * @return SubLocalityLevel
     *
     * @throws CollectionIsEmpty
     */
    public function first()
    {
        if (empty($this->subLocalityLevels)) {
            throw new CollectionIsEmpty();
        }

        return reset($this->subLocalityLevels);
    }

    /**
     * @param int      $offset
     * @param int|null $length
     *
     * @return SubLocalityLevel[]
     */
    public function slice(int $offset, int $length = null)
    {
        return array_slice($this->subLocalityLevels, $offset, $length, true);
    }

    /**
     * @param int $level
     *
     * @return bool
     */
    public function has(int $level)
    {
        return isset($this->subLocalityLevels[$level]);
    }

    /**
     * @param int $level
     *
     * @return SubLocalityLevel
     *
     * @throws \OutOfBoundsException
     * @throws InvalidArgument
     */
    public function get(int $level)
    {
        $this->checkLevel($level);

        if (!isset($this->subLocalityLevels[$level])) {
            throw new InvalidArgument(sprintf('SubLocality level %d is not set for this address', $level));
        }

        return  $this->subLocalityLevels[$level];
    }

    /**
     * @return SubLocalityLevel[]
     */
    public function all()
    {
        return $this->subLocalityLevels;
    }

    /**
     * @param int $level
     *
     * @throws \OutOfBoundsException
     */
    private function checkLevel(int $level)
    {
        if ($level <= 0 || $level > self::MAX_LEVEL_DEPTH) {
            throw new OutOfBounds(
                sprintf('SubLocality level should be an integer in [1,%d], %d given', self::MAX_LEVEL_DEPTH, $level)
            );
        }
    }
}
