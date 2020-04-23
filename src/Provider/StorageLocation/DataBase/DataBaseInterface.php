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

use Geocoder\Model\Address;
use Geocoder\Provider\StorageLocation\Model\DBConfig;
use Geocoder\Provider\StorageLocation\Model\Place;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
interface DataBaseInterface
{
    public function __construct($databaseProvider, DBConfig $dbConfig);

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
     * @param int    $page
     * @param int    $maxResults
     * @param string $locale
     *
     * @return Place[]
     */
    public function get(string $searchKey, int $page = 0, int $maxResults = 30, string $locale = ''): array;

    /**
     * @param Place $place
     *
     * @return bool
     */
    public function delete(Place $place): bool;

    /**
     * As findAll in repository
     *
     * @param int $offset
     * @param int $limit
     *
     * @return Place[]
     */
    public function getAllPlaces(int $offset = 0, int $limit = 50): array;

    /**
     * All admin levels what contain database
     *
     * @return int[]
     */
    public function getAdminLevels(): array;

    /**
     * Current db configuration
     *
     * @return DBConfig
     */
    public function getDbConfig(): DBConfig;

    /**
     * Compile keys for each Address entity in Place's collection
     *
     * @param Place $place
     * @param bool  $useLevels
     * @param bool  $usePrefix
     * @param bool  $useAddress
     *
     * @return string[]
     */
    public function compileKeys(
        Place $place,
        bool $useLevels = true,
        bool $usePrefix = true,
        bool $useAddress = true
    ): array;

    /**
     * Compile key for specific Address entity
     *
     * @param Address $address
     * @param bool    $useLevels
     * @param bool    $usePrefix
     * @param bool    $useAddress
     *
     * @return string
     */
    public function compileKey(
        Address $address,
        bool $useLevels = true,
        bool $usePrefix = true,
        bool $useAddress = true
    ): string;
}
