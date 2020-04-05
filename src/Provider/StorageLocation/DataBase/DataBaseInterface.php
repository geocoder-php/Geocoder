<?php
declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */
namespace Geocoder\Provider\StorageLocation\DataBase;

use Geocoder\Provider\StorageLocation\Model\DBConfig;
use Geocoder\Provider\StorageLocation\Model\Place;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
interface DataBaseInterface
{
    public function __construct(CacheItemPoolInterface $cacheProvider, DBConfig $dbConfig);

    /**
     * @param Place $place
     *
     * @return bool
     */
    public function add(Place $place): bool;

    /**
     * @param Place $place
     *
     * @return bool
     */
    public function update(Place $place): bool;

    /**
     * @param string $searchKey
     *
     * @return Place[]
     */
    public function get(string $searchKey): array;

    /**
     * @param Place $place
     *
     * @return bool
     */
    public function delete(Place $place): bool;

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return Place[]
     */
    public function getAllPlaces(int $offset = 0, int $limit = 50): array;

    /**
     * @return int[]
     */
    public function getAdminLevels(): array;

    /**
     * @return DBConfig
     */
    public function getDbConfig(): DBConfig;

    /**
     * @param Place $place
     * @param bool  $useLevels
     * @param bool  $usePrefix
     * @param bool  $useAddress
     *
     * @return string
     */
    public function compileKey(Place $place,
        bool $useLevels = true,
        bool $usePrefix = true,
        bool $useAddress = true): string;
}
