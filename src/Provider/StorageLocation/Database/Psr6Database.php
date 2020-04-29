<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\StorageLocation\Database;

use Geocoder\Provider\StorageLocation\Model\DBConfig;
use Geocoder\Provider\StorageLocation\Model\Place;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\InvalidArgumentException;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
class Psr6Database extends AbstractDatabase implements DataBaseInterface
{
    /**
     * By that keys we will store hashes (references) to fetch real object
     *
     * @var string[][]
     */
    protected $actualKeys = [];

    /**
     * By that keys we will store real Place objects
     *
     * @var bool[]
     */
    protected $objectsHashes = [];

    /**
     * Sorted array of admin levels what used for stored data
     *
     * @var bool[]
     */
    protected $existAdminLevels = [];

    /**
     * @var DBConfig
     */
    protected $dbConfig;

    /**
     * @var CacheItemPoolInterface
     */
    protected $databaseProvider;

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

        parent::__construct($databaseProvider, $dbConfig);

        $this->getActualKeys();
        $this->getExistAdminLevels();
        $this->getExistHashKeys();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function add(Place $place): bool
    {
        $place->setObjectHash('');
        $place->setObjectHash(spl_object_hash($place));

        $this->savePlace($place);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function update(Place $place): bool
    {
        $this->savePlace($place);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get(string $searchKey, int $page = 0, int $maxResults = 30, string $locale = ''): array
    {
        if ($maxResults > $this->dbConfig->getMaxPlacesInOneResponse()) {
            $maxResults = $this->dbConfig->getMaxPlacesInOneResponse();
        }

        if ('' === $locale) {
            $locale = $this->dbConfig->getDefaultLocale();
        }

        $result = [];

        foreach ($this->makeSearch($searchKey, $page, $maxResults, $locale) as $key) {
            $item = $this->databaseProvider->getItem($this->actualKeys[$locale][$key]);
            if ($item->isHit()) {
                $this->dbConfig->isUseCompression() ?
                    $rawData = json_decode(gzuncompress($item->get()), true) :
                    $rawData = json_decode($item->get(), true);
                is_array($rawData) ? $result[$key] = (Place::createFromArray($rawData, [$locale])) : $result[$key] = null;
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
        $tempArray = array_keys($this->objectsHashes);

        reset($tempArray);
        for ($i = 0; $i < $offset; ++$i) {
            next($tempArray);
        }

        $counter = 0;
        foreach ($tempArray as $item) {
            $item = $this->databaseProvider->getItem($item);
            if ($item->isHit()) {
                $this->dbConfig->isUseCompression() ?
                    $rawData = json_decode(gzuncompress($item->get()), true) :
                    $rawData = json_decode($item->get(), true);
                $result[] = Place::createFromArray($rawData);
            }

            ++$counter;
            if ($counter >= $limit) {
                break;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function delete(Place $place): bool
    {
        $rawData = json_encode($place->toArray());

        $item = $this->databaseProvider->getItem($place->getObjectHash());
        $item->expiresAfter(new \DateInterval('PT0S'));
        $item->set($rawData);

        $this->databaseProvider->save($item);

        foreach ($this->actualKeys as $locale => $keys) {
            $place->selectLocale($locale);
            $keyForDelete = $this->compileKey($place->getSelectedAddress());
            if (isset($keys[$keyForDelete])) {
                unset($this->actualKeys[$locale][$keyForDelete]);
                $this->updateActualKeys();
            }
        }

        unset($this->objectsHashes[$place->getObjectHash()]);
        $this->updateHashKeys();

        return true;
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
    private function updateHashKeys(): bool
    {
        $this->updateServiceKey($this->dbConfig->getKeyForHashKeys(), json_encode($this->objectsHashes));

        return true;
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

    private function getExistHashKeys(): bool
    {
        $rawHashes = $this->getServiceKey($this->dbConfig->getKeyForHashKeys());

        if ($rawHashes) {
            $this->objectsHashes = json_decode($rawHashes, true);

            return true;
        }

        return false;
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
    function updateExistAdminLevels(): bool
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
            return $this->dbConfig->isUseCompression() ? gzuncompress($item->get()) : $item->get();
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

        $this->dbConfig->isUseCompression() ?
            $item->set(gzcompress($data, $this->dbConfig->getCompressionLevel())) : $item->set($data);

        $this->databaseProvider->save($item);

        return true;
    }

    private function savePlace(Place $place): bool
    {
        $rawData = json_encode($place->toArray());

        if ($this->dbConfig->isUseCompression()) {
            $rawData = gzcompress($rawData, $this->dbConfig->getCompressionLevel());
        }

        $item = $this->databaseProvider->getItem($place->getObjectHash());

        $item->expiresAfter($this->dbConfig->getTtlForRecord());
        $item->set($rawData);

        $this->databaseProvider->save($item);

        $this->objectsHashes[$place->getObjectHash()] = true;
        $this->updateHashKeys();

        foreach ($this->compileKeys($place) as $locale => $key) {
            $this->actualKeys[$locale][$key] = $place->getObjectHash();
        }
        $this->updateActualKeys();

        return true;
    }
}
