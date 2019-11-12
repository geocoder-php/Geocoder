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

use Geocoder\Exception\CollectionIsEmpty;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\OutOfBounds;

/**
 * @author Giorgio Premi <giosh94mhz@gmail.com>
 */
final class AdminLevelCollection implements \IteratorAggregate, \Countable
{
    const MAX_LEVEL_DEPTH = 5;

    /**
     * @var AdminLevel[]
     */
    private $adminLevels;

    /**
     * @param AdminLevel[] $adminLevels
     */
    public function __construct(array $adminLevels = [])
    {
        $this->adminLevels = [];

        foreach ($adminLevels as $adminLevel) {
            $level = $adminLevel->getLevel();

            $this->checkLevel($level);

            if ($this->has($level)) {
                throw new InvalidArgument(sprintf('Administrative level %d is defined twice', $level));
            }

            $this->adminLevels[$level] = $adminLevel;
        }

        ksort($this->adminLevels, SORT_NUMERIC);
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
        return count($this->adminLevels);
    }

    /**
     * @return AdminLevel
     *
     * @throws CollectionIsEmpty
     */
    public function first(): AdminLevel
    {
        if (empty($this->adminLevels)) {
            throw new CollectionIsEmpty();
        }

        return reset($this->adminLevels);
    }

    /**
     * @param int      $offset
     * @param int|null $length
     *
     * @return AdminLevel[]
     */
    public function slice(int $offset, int $length = null): array
    {
        return array_slice($this->adminLevels, $offset, $length, true);
    }

    /**
     * @return bool
     */
    public function has(int $level): bool
    {
        return isset($this->adminLevels[$level]);
    }

    /**
     * @return AdminLevel
     *
     * @throws \OutOfBoundsException
     * @throws InvalidArgument
     */
    public function get(int $level): AdminLevel
    {
        $this->checkLevel($level);

        if (!isset($this->adminLevels[$level])) {
            throw new InvalidArgument(sprintf('Administrative level %d is not set for this address', $level));
        }

        return  $this->adminLevels[$level];
    }

    /**
     * @return AdminLevel[]
     */
    public function all(): array
    {
        return $this->adminLevels;
    }

    /**
     * @param int $level
     *
     * @throws \OutOfBoundsException
     */
    private function checkLevel(int $level)
    {
        if ($level <= 0 || $level > self::MAX_LEVEL_DEPTH) {
            throw new OutOfBounds(sprintf('Administrative level should be an integer in [1,%d], %d given', self::MAX_LEVEL_DEPTH, $level));
        }
    }
}
