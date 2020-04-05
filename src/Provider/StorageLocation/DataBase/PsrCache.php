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
    private $cacheProvider;

    /**
     * PsrCache constructor.
     *
     * @param CacheItemPoolInterface $cacheProvider
     * @param DBConfig               $dbConfig
     */
    public function __construct($cacheProvider, DBConfig $dbConfig)
    {
        if (!($cacheProvider instanceof CacheItemPoolInterface)) {
            throw new InvalidArgumentException('Cache provider should be instance of '.CacheItemPoolInterface::class);
        }

        $this->cacheProvider = $cacheProvider;
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

        $item = $this->cacheProvider->getItem($itemName);
        $item->expiresAfter($this->dbConfig->getTtlForRecord());
        $item->set($rawData);

        $this->cacheProvider->save($item);

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

        $item = $this->cacheProvider->getItem($itemName);
        if (!$item->isHit()) {
            $this->actualKeys[] = $itemName;
            $this->updateActualKeys();
        }
        $item->expiresAfter($this->dbConfig->getTtlForRecord());
        $item->set($rawData);

        $this->cacheProvider->save($item);

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
            $item = $this->cacheProvider->getItem($key);
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
            $item = $this->cacheProvider->getItem($item);
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
        $item = $this->cacheProvider->getItem($itemName);
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

    public function normalizeStringForKeyName(string $rawString)
    {
        return rawurlencode(
            mb_strtolower(
                trim($rawString)
            )
        );
    }

    private function getActualKeys(): bool
    {
        $rawKeys = $this->getServiceKey($this->dbConfig->getKeyForDumpKeys());
        if ($rawKeys) {
            $this->actualKeys = json_decode($rawKeys, true);
            return true;
        }

        return false;
    }

    private function updateActualKeys(): bool
    {
        $this->updateServiceKey($this->dbConfig->getKeyForDumpKeys(), json_encode($this->actualKeys));

        return true;
    }

    private function getExistAdminLevels(): bool
    {
        $rawLevels = $this->getServiceKey($this->dbConfig->getKeyForDumpKeys());
        if ($rawLevels) {
            $this->existAdminLevels = json_decode($rawLevels, true);

            return true;
        }

        return false;
    }

    private function updateExistAdminLevels(): bool
    {
        $this->updateServiceKey($this->dbConfig->getKeyForAdminLevels(), json_encode($this->existAdminLevels));

        return true;
    }

    private function getServiceKey(string $key)
    {
        $item = $this->cacheProvider->getItem(implode(
            $this->dbConfig->getGlueForSections(),
            array_merge($this->dbConfig->getGlobalPrefix(), [$key])
        ));
        if ($item->isHit()) {
            return $item->get();
        }

        return false;
    }

    private function updateServiceKey(string $key, string $data): bool
    {
        $item = $this->cacheProvider->getItem(implode(
            $this->dbConfig->getGlueForSections(),
            array_merge($this->dbConfig->getGlobalPrefix(), [$key])
        ));
        $item->expiresAfter($this->dbConfig->getTtlForRecord());
        $item->set($data);

        return true;
    }

    private function makeSearch(string $phrase, int $page, int $maxResults): array
    {
        $result = [];

        foreach ($this->actualKeys as $actualKey) {
            $grade = $this->evaluateHitPhrase($phrase, $actualKey);
            if ($grade > -1 && $grade < strlen($phrase)) {
                $result[$actualKey] = $grade;
            }
        }
        asort($result);
        if (count($result) > ($page * $maxResults)) {
            $result = array_slice($result, ($page * $maxResults), $maxResults);
        }

        return array_keys($result);
    }

    private function evaluateHitPhrase(string $phrase, string $original): int
    {
        $subPhrases = explode(',', rawurldecode($phrase));

        $finalSubPhrases = [];
        foreach ($subPhrases as $subPhrase) {
            $finalSubPhrases = array_merge($finalSubPhrases, explode(' ', $subPhrase));
        }

        $lengthOfOriginal = mb_strlen($original);

        $result = null;
        foreach ($finalSubPhrases as $subPhrase) {
            if (mb_strlen($subPhrase) < 2) {
                continue;
            }
            $search = mb_strpos($original, $subPhrase);

            if (is_int($search)) {
                $tempOriginal = mb_substr($original, 0, $search).mb_substr($original, $search + mb_strlen($subPhrase));

                if (is_null($result)) {
                    $result = 0;
                }

                $result += mb_strlen($subPhrase) + mb_strlen($tempOriginal) - $lengthOfOriginal;
            }
        }

        return is_null($result) ? mb_strlen($phrase) : $result;
    }
}
