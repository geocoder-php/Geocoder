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

use Geocoder\Model\AdminLevel;
use Geocoder\Provider\StorageLocation\Model\DBConfig;
use Geocoder\Provider\StorageLocation\Model\Place;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\InvalidArgumentException;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
class PsrCache implements DataBaseInterface
{
    /**
     * Buffer for actual keys
     *
     * @var string[]
     */
    private $actualKeys = [];

    /**
     * Sorted array of admin levels what used for stored data
     *
     * @var array
     */
    private $existAdminLevels = [];

    /**
     * @var DBConfig
     */
    private $dbConfig;

    /**
     * @var CacheItemPoolInterface
     */
    private $databaseProvider;

    /**
     * PsrCache constructor.
     *
     * @param CacheItemPoolInterface $databaseProvider
     * @param DBConfig               $dbConfig
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function __construct($databaseProvider, DBConfig $dbConfig)
    {
        if (!($databaseProvider instanceof CacheItemPoolInterface)) {
            throw new InvalidArgumentException('Cache provider should be instance of '.CacheItemPoolInterface::class);
        }

        $this->databaseProvider = $databaseProvider;
        $this->dbConfig = $dbConfig;

        $this->getActualKeys();
        $this->getExistAdminLevels();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function add(Place $place): bool
    {
        $rawData = json_encode($place->toArray());

        $itemName = $this->compileKey($place);

        $item = $this->databaseProvider->getItem($itemName);
        $item->expiresAfter($this->dbConfig->getTtlForRecord());
        $item->set($rawData);

        $this->databaseProvider->save($item);

        $this->actualKeys[] = $itemName;
        $this->updateActualKeys();

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function update(Place $place): bool
    {
        $rawData = json_encode($place->toArray());

        $itemName = $this->compileKey($place);

        $item = $this->databaseProvider->getItem($itemName);
        if (!$item->isHit()) {
            $this->actualKeys[] = $itemName;
            $this->updateActualKeys();
        }
        $item->expiresAfter($this->dbConfig->getTtlForRecord());
        $item->set($rawData);

        $this->databaseProvider->save($item);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get(string $searchKey, int $page = 0, int $maxResults = 30): array
    {
        if ($maxResults > $this->dbConfig->getMaxPlacesInOneResponse()) {
            $maxResults = $this->dbConfig->getMaxPlacesInOneResponse();
        }

        $result = [];

        foreach ($this->makeSearch($searchKey, $page, $maxResults) as $key) {
            $item = $this->databaseProvider->getItem($key);
            if ($item->isHit()) {
                $rawData = json_decode($item->get(), true);
                $result[$key] = (Place::createFromArray($rawData));
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getAllPlaces(int $offset = 0, int $limit = 50): array
    {
        if ($offset > count($this->actualKeys)) {
            return [];
        }

        if ($limit > $this->dbConfig->getMaxPlacesInOneResponse()) {
            $limit = $this->dbConfig->getMaxPlacesInOneResponse();
        }

        $result = [];
        $tempArray = $this->actualKeys;

        reset($tempArray);
        for ($i = 0; $i < $offset; ++$i) {
            next($tempArray);
        }

        $counter = 0;
        foreach ($tempArray as $item) {
            $item = $this->databaseProvider->getItem($item);
            if ($item->isHit()) {
                $rawData = json_decode($item->get(), true);
                $result[] = (Place::createFromArray($rawData))->setPolygonsFromArray($rawData['polygons']);
            }

            ++$counter;
            if ($counter >= $limit) {
                break;
            }
        }

        return $result;
    }

    /**
     * @return int[]
     */
    public function getAdminLevels(): array
    {
        return array_keys($this->existAdminLevels);
    }

    /**
     * @return DBConfig
     */
    public function getDbConfig(): DBConfig
    {
        return $this->dbConfig;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function delete(Place $place): bool
    {
        $rawData = json_encode($place->toArray());

        $itemName = $this->compileKey($place);
        $item = $this->databaseProvider->getItem($itemName);
        $item->expiresAfter(new \DateInterval('PT0S'));
        $item->set($rawData);

        $searchResult = array_search($itemName, $this->actualKeys);
        if (is_int($searchResult)) {
            unset($this->actualKeys[$searchResult]);
            $this->updateActualKeys();
        }

        return true;
    }

    /**
     * Compile key name for Place entity
     *
     * @param Place $place
     * @param bool  $useLevels
     * @param bool  $usePrefix
     * @param bool  $useAddress
     *
     * @return string
     *
     * @example 'geocoder.storage-provider.level-0-ukraine-ua.level-1-kyiv-.ua.01000.kyiv.nezalezhnosti sq.3'
     *              ^           ^                                                       - content of @see DBConfig::GLOBAL_PREFIX array
     *                                           ^                                      - max level for that Place object
     *                                              ^    ^    ^     ^              ^    - compiled Place's fields
     * @example 'geocoder.storage-provider.ua.01000.kyiv.nezalezhnosti sq.3'
     *              ^           ^                                               - content of @see DBConfig::GLOBAL_PREFIX array
     *                                     ^    ^    ^              ^     ^     - compiled Place's fields
     * @example 'ua.01000.kyiv.nezalezhnosti sq.3'
     *            ^    ^     ^              ^   ^                               - compiled Place's fields
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function compileKey(
        Place $place,
        bool $useLevels = true,
        bool $usePrefix = true,
        bool $useAddress = true
    ): string {
        return implode(
            $this->dbConfig->getGlueForSections(),
            array_merge(
                $usePrefix ? $this->dbConfig->getGlobalPrefix() : [],
                $useLevels ? $this->compileLevelsForKey($place) : [],
                $useAddress ? $this->compileAddressForKey($place) : []
            )
        );
    }

    /**
     * Levels compiler for forming identifier for Place entity in @see compileKey
     *
     * @param Place $place
     *
     * @return string[]
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function compileLevelsForKey(Place $place): array
    {
        $levels = [];

        /** @var AdminLevel $level */
        foreach ($place->getAdminLevels() as $level) {
            $levels[$level->getLevel()] = implode($this->dbConfig->getGlueForLevel(), [
                $this->dbConfig->getPrefixLevel(),
                $level->getLevel(),
                $this->normalizeStringForKeyName($level->getName()),
                $this->normalizeStringForKeyName((string) $level->getCode()),
            ]);

            if (!isset($this->existAdminLevels[$level->getLevel()])) {
                $this->existAdminLevels[$level->getLevel()] = true;
                ksort($this->existAdminLevels);
                $this->updateExistAdminLevels();
            }
        }

        ksort($levels);

        return $levels;
    }

    /**
     * Address compiler for forming identifier for Place entity in @see compileKey
     *
     * @param Place $place
     *
     * @return string[]
     */
    private function compileAddressForKey(Place $place): array
    {
        return [
            $this->normalizeStringForKeyName($place->getCountry()->getCode()),
            $this->normalizeStringForKeyName($place->getPostalCode()),
            $this->normalizeStringForKeyName($place->getLocality()),
            $this->normalizeStringForKeyName($place->getSubLocality()),
            $this->normalizeStringForKeyName($place->getStreetName()),
            $this->normalizeStringForKeyName($place->getStreetNumber()),
        ];
    }

    /**
     * @param string $rawString
     *
     * @return string
     */
    public function normalizeStringForKeyName(string $rawString)
    {
        return rawurlencode(
            mb_strtolower(
                trim($rawString)
            )
        );
    }

    /**
     * @return bool
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getActualKeys(): bool
    {
        $rawKeys = $this->getServiceKey($this->dbConfig->getKeyForDumpKeys());
        if ($rawKeys) {
            $this->actualKeys = json_decode($rawKeys, true);

            return true;
        }

        return false;
    }

    /**
     * @return bool
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function updateActualKeys(): bool
    {
        $this->updateServiceKey($this->dbConfig->getKeyForDumpKeys(), json_encode($this->actualKeys));

        return true;
    }

    /**
     * @return bool
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getExistAdminLevels(): bool
    {
        $rawLevels = $this->getServiceKey($this->dbConfig->getKeyForDumpKeys());
        if ($rawLevels) {
            $this->existAdminLevels = json_decode($rawLevels, true);

            return true;
        }

        return false;
    }

    /**
     * @return bool
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function updateExistAdminLevels(): bool
    {
        $this->updateServiceKey($this->dbConfig->getKeyForAdminLevels(), json_encode($this->existAdminLevels));

        return true;
    }

    /**
     * @param string $key
     *
     * @return bool|mixed
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getServiceKey(string $key)
    {
        $item = $this->databaseProvider->getItem(implode(
            $this->dbConfig->getGlueForSections(),
            array_merge($this->dbConfig->getGlobalPrefix(), [$key])
        ));
        if ($item->isHit()) {
            return $item->get();
        }

        return false;
    }

    /**
     * @param string $key
     * @param string $data
     *
     * @return bool
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function updateServiceKey(string $key, string $data): bool
    {
        $item = $this->databaseProvider->getItem(implode(
            $this->dbConfig->getGlueForSections(),
            array_merge($this->dbConfig->getGlobalPrefix(), [$key])
        ));
        $item->expiresAfter($this->dbConfig->getTtlForRecord());
        $item->set($data);

        return true;
    }

    /**
     * Search in each key, needed phrase @see get
     * Returning all keys what fitable for phrase
     *
     * @param string $phrase
     * @param int    $page
     * @param int    $maxResults
     *
     * @return string[]
     */
    private function makeSearch(string $phrase, int $page, int $maxResults): array
    {
        $result = [];

        foreach ($this->actualKeys as $actualKey) {
            $grade = $this->evaluateHitPhrase($phrase, $actualKey);
            if ($grade > 0) {
                $result[$actualKey] = $grade;
            }
        }
        arsort($result);
        if (count($result) > ($page * $maxResults)) {
            $result = array_slice($result, ($page * $maxResults), $maxResults);
        }

        return array_keys($result);
    }

    /**
     * Evaluate original regarding to phrase. Less mark value is better. @see makeSearch
     *
     * @param string $phrase
     * @param string $original
     *
     * @return int
     */
    private function evaluateHitPhrase(string $phrase, string $original): int
    {
        $phrase = rawurldecode($phrase);
        $original = substr($original, strlen(implode(
            $this->dbConfig->getGlueForSections(),
            $this->dbConfig->getGlobalPrefix()
        )) + 1);

        $result = 0;
        foreach ([',', ' ', '.'] as $delimiter) {
            foreach (explode($delimiter, $phrase) as $symbols) {
                if (empty($symbols)) {
                    continue;
                }
                $result += substr_count($original, $symbols);
            }
        }

        return $result;
    }
}
