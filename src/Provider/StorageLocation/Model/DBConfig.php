<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\StorageLocation\Model;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
final class DBConfig
{
    const GLOBAL_PREFIX = ['geocoder', 'storageProvider'];

    const KEY_FOR_DUMP_KEYS = 'dump-keys';

    const KEY_FOR_HASH_KEYS = 'hash-keys';

    const PREFIX_LEVEL = 'level';

    const KEY_FOR_ADMIN_LEVELS = 'exist-admin-levels';

    const GLUE_FOR_SECTIONS = '_';

    const GLUE_FOR_LEVEL = '-';

    const TTL_FOR_RECORD = 'P365D';

    const MAX_PLACES_IN_ONE_RESPONSE = 100;

    const DEFAULT_LOCALE = 'en';

    const USE_COMPRESSION = false;

    const COMPRESSION_LEVEL = 5;

    /**
     * That prefix will be use before all keys what will store database driver
     *
     * @var array
     */
    private $globalPrefix;

    /**
     * That key will be use for name key of record in db where will store all references (hashes) to records in database
     *
     * @var string
     */
    private $keyForDumpKeys;

    /**
     * That key will be use for name key of record in db where will store all relevant records in database
     *
     * @var string
     */
    private $keyForHashKeys;

    /**
     * That prefix will be use for identify level section in record's key
     *
     * @var string
     */
    private $prefixLevel;

    /**
     * That key will be use for name key of record in db where will store all relevant admin levels in database
     *
     * @var string
     */
    private $keyForAdminLevels;

    /**
     * That string will be use for glue sections between each other for compile key for specific record
     *
     * @var string
     */
    private $glueForSections;

    /**
     * That string will be use for glue inside level sections in key of record
     *
     * @var string
     */
    private $glueForLevel;

    /**
     * Time interval for evaluate valid records in database
     *
     * @var \DateInterval
     */
    private $ttlForRecord;

    /**
     * Maximum quantity of Places what can be responded from Db
     *
     * @var int
     */
    private $maxPlacesInOneResponse;

    /**
     * Locale alias what will be default for queries
     *
     * @var string
     */
    private $defaultLocale;

    /**
     * Use compression for store value. Using @see gzcompress(), gzuncompress() functionality
     *
     * @var bool
     */
    private $useCompression;

    /**
     * Compression level according to @see gzcompress()
     *
     * @var int
     */
    private $compressionLevel;

    public function __construct(
        array $globalPrefix = self::GLOBAL_PREFIX,
        string $keyForDumpKeys = self::KEY_FOR_DUMP_KEYS,
        string $keyForHashKeys = self::KEY_FOR_HASH_KEYS,
        string $prefixLevel = self::PREFIX_LEVEL,
        string $keyForAdminLevels = self::KEY_FOR_ADMIN_LEVELS,
        string $glueForSections = self::GLUE_FOR_SECTIONS,
        string $glueForLevel = self::GLUE_FOR_LEVEL,
        string $ttlForRecord = self::TTL_FOR_RECORD,
        int $maxPlacesInOneResponse = self::MAX_PLACES_IN_ONE_RESPONSE,
        string $defaultLocale = self::DEFAULT_LOCALE,
        bool $useCompression = self::USE_COMPRESSION,
        int $compressionLevel = self::COMPRESSION_LEVEL
    ) {
        $this->globalPrefix = $globalPrefix;
        $this->keyForDumpKeys = $keyForDumpKeys;
        $this->keyForHashKeys = $keyForHashKeys;
        $this->prefixLevel = $prefixLevel;
        $this->keyForAdminLevels = $keyForAdminLevels;
        $this->glueForSections = $glueForSections;
        $this->glueForLevel = $glueForLevel;
        $this->ttlForRecord = new \DateInterval($ttlForRecord);
        $this->maxPlacesInOneResponse = $maxPlacesInOneResponse;
        $this->defaultLocale = $defaultLocale;
        $this->useCompression = $useCompression;
        $this->compressionLevel = $compressionLevel;
    }

    /**
     * @return array
     */
    public function getGlobalPrefix(): array
    {
        return $this->globalPrefix;
    }

    /**
     * @param array $globalPrefix
     *
     * @return DBConfig
     */
    public function setGlobalPrefix(array $globalPrefix): self
    {
        $this->globalPrefix = $globalPrefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getKeyForDumpKeys(): string
    {
        return $this->keyForDumpKeys;
    }

    /**
     * @param string $keyForDumpKeys
     *
     * @return DBConfig
     */
    public function setKeyForDumpKeys(string $keyForDumpKeys): self
    {
        $this->keyForDumpKeys = $keyForDumpKeys;

        return $this;
    }

    /**
     * @return string
     */
    public function getKeyForHashKeys(): string
    {
        return $this->keyForHashKeys;
    }

    /**
     * @param string $keyForHashKeys
     *
     * @return DBConfig
     */
    public function setKeyForHashKeys(string $keyForHashKeys): self
    {
        $this->keyForHashKeys = $keyForHashKeys;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrefixLevel(): string
    {
        return $this->prefixLevel;
    }

    /**
     * @param string $prefixLevel
     *
     * @return DBConfig
     */
    public function setPrefixLevel(string $prefixLevel): self
    {
        $this->prefixLevel = $prefixLevel;

        return $this;
    }

    /**
     * @return string
     */
    public function getKeyForAdminLevels(): string
    {
        return $this->keyForAdminLevels;
    }

    /**
     * @param string $keyForAdminLevels
     *
     * @return DBConfig
     */
    public function setKeyForAdminLevels(string $keyForAdminLevels): self
    {
        $this->keyForAdminLevels = $keyForAdminLevels;

        return $this;
    }

    /**
     * @return string
     */
    public function getGlueForSections(): string
    {
        return $this->glueForSections;
    }

    /**
     * @param string $glueForSections
     *
     * @return DBConfig
     */
    public function setGlueForSections(string $glueForSections): self
    {
        $this->glueForSections = $glueForSections;

        return $this;
    }

    /**
     * @return string
     */
    public function getGlueForLevel(): string
    {
        return $this->glueForLevel;
    }

    /**
     * @param string $glueForLevel
     *
     * @return DBConfig
     */
    public function setGlueForLevel(string $glueForLevel): self
    {
        $this->glueForLevel = $glueForLevel;

        return $this;
    }

    /**
     * @return \DateInterval
     */
    public function getTtlForRecord(): \DateInterval
    {
        return $this->ttlForRecord;
    }

    /**
     * @param \DateInterval $ttlForRecord
     *
     * @return DBConfig
     */
    public function setTtlForRecord(\DateInterval $ttlForRecord): self
    {
        $this->ttlForRecord = $ttlForRecord;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxPlacesInOneResponse(): int
    {
        return $this->maxPlacesInOneResponse;
    }

    /**
     * @param int $maxPlacesInOneResponse
     */
    public function setMaxPlacesInOneResponse(int $maxPlacesInOneResponse)
    {
        $this->maxPlacesInOneResponse = $maxPlacesInOneResponse;
    }

    /**
     * @return string
     */
    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    /**
     * @param string $defaultLocale
     *
     * @return DBConfig
     */
    public function setDefaultLocale(string $defaultLocale): self
    {
        $this->defaultLocale = $defaultLocale;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUseCompression(): bool
    {
        return $this->useCompression;
    }

    /**
     * @param bool $useCompression
     *
     * @return DBConfig
     */
    public function setUseCompression(bool $useCompression): self
    {
        $this->useCompression = $useCompression;

        return $this;
    }

    /**
     * @return int
     */
    public function getCompressionLevel(): int
    {
        return $this->compressionLevel;
    }

    /**
     * @param int $compressionLevel
     *
     * @return DBConfig
     */
    public function setCompressionLevel(int $compressionLevel): self
    {
        $this->compressionLevel = $compressionLevel;

        return $this;
    }
}
