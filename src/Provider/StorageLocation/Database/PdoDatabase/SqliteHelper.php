<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\StorageLocation\Database\PdoDatabase;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
final class SqliteHelper implements HelperInterface
{
    private $prefix;

    public function __construct(string $prefix = '')
    {
        $this->prefix = $prefix;
    }

    public function queryForCreateTables(): array
    {
        return [
            'CREATE TABLE IF NOT EXISTS "'.$this->prefix.'place" (
	"'.Constants::OBJECT_HASH.'"	TEXT NOT NULL UNIQUE,
	"'.Constants::COMPRESSED_DATA.'"	BLOB,
	PRIMARY KEY("'.Constants::OBJECT_HASH.'")
)',
            'CREATE TABLE IF NOT EXISTS "'.$this->prefix.'actual_keys" (
	"'.Constants::OBJECT_HASH.'"	TEXT,
	"'.Constants::LOCALE.'"	TEXT,
	"'.Constants::SEARCH_TEXT.'"	TEXT,
	FOREIGN KEY("object_hash") REFERENCES "'.$this->prefix.'place"("object_hash")
)',
            'CREATE TABLE IF NOT EXISTS "'.$this->prefix.'address" (
	"'.Constants::OBJECT_HASH.'"	TEXT,
	"'.Constants::LOCALE.'"	TEXT,
	"'.Constants::PROVIDED_BY.'"	TEXT,
	"'.Constants::COORDINATE_LATITUDE.'"	REAL,
	"'.Constants::COORDINATE_LONGITUDE.'"	REAL,
	"'.Constants::BOUNDS_SOUTH.'"	REAL,
	"'.Constants::BOUNDS_WEST.'"	REAL,
	"'.Constants::BOUNDS_NORTH.'"	REAL,
	"'.Constants::BOUNDS_EAST.'"	REAL,
	"'.Constants::STREET_NUMBER.'"	TEXT,
	"'.Constants::STREET_NAME.'"	TEXT,
	"'.Constants::POSTAL_CODE.'"	TEXT,
	"'.Constants::LOCALITY.'"	TEXT,
	"'.Constants::SUB_LOCALITY.'"	TEXT,
	"'.Constants::COUNTRY_CODE.'"	TEXT,
	"'.Constants::COUNTY_NAME.'"	TEXT,
	"'.Constants::TIMEZONE.'"	TEXT,
	FOREIGN KEY("'.Constants::OBJECT_HASH.'") REFERENCES "'.$this->prefix.'place"("'.Constants::OBJECT_HASH.'")
)',
            'CREATE TABLE IF NOT EXISTS "'.$this->prefix.'admin_level" (
	"'.Constants::OBJECT_HASH.'"	TEXT,
	"'.Constants::LOCALE.'"	TEXT,
	"'.Constants::LEVEL.'"	INTEGER,
	"'.Constants::NAME.'"	TEXT,
	"'.Constants::CODE.'"	TEXT,
	FOREIGN KEY("'.Constants::OBJECT_HASH.'") REFERENCES "'.$this->prefix.'place"("'.Constants::OBJECT_HASH.'")
)',
            'CREATE TABLE IF NOT EXISTS "'.$this->prefix.'polygon" (
	"'.Constants::OBJECT_HASH.'"	TEXT,
	"'.Constants::POLYGON_NUMBER.'"	INTEGER,
	"'.Constants::POINT_NUMBER.'"	INTEGER,
	"'.Constants::LONGITUDE.'"	REAL,
	"'.Constants::LATITUDE.'"	REAL,
	FOREIGN KEY("'.Constants::OBJECT_HASH.'") REFERENCES "'.$this->prefix.'place"("'.Constants::OBJECT_HASH.'")
)',
            'CREATE UNIQUE INDEX IF NOT EXISTS "actual_keys_id" ON "'.$this->prefix.'actual_keys" (
	"'.Constants::OBJECT_HASH.'"	ASC,
	"'.Constants::LOCALE.'"	ASC
)',
            'CREATE UNIQUE INDEX IF NOT EXISTS  "address_id" ON "'.$this->prefix.'address" (
	"'.Constants::OBJECT_HASH.'"	ASC,
	"'.Constants::LOCALE.'"	ASC
)',
            'CREATE UNIQUE INDEX IF NOT EXISTS  "admin_level_id" ON "'.$this->prefix.'admin_level" (
	"'.Constants::OBJECT_HASH.'"	ASC,
	"'.Constants::LOCALE.'"	ASC,
	"'.Constants::LEVEL.'"	ASC
)',
            'CREATE UNIQUE INDEX IF NOT EXISTS  "polygon_id" ON "'.$this->prefix.'polygon" (
	"'.Constants::OBJECT_HASH.'"	ASC,
	"'.Constants::POLYGON_NUMBER.'"	ASC,
	"'.Constants::POINT_NUMBER.'"	ASC
)',
        ];
    }

    public function queryGetAllPlaces(): string
    {
        return 'SELECT '.Constants::OBJECT_HASH.' FROM '.$this->prefix.'place LIMIT :offset, :limit';
    }

    public function queryGetAllAdminLevels(): string
    {
        return 'SELECT DISTINCT('.Constants::LEVEL.') as '.Constants::LEVEL.' FROM '.$this->prefix.'admin_level';
    }

    public function queryGetAllActualKeys(): string
    {
        return 'SELECT '.Constants::OBJECT_HASH.', '.Constants::LOCALE.', '.Constants::SEARCH_TEXT.
            ' FROM '.$this->prefix.'actual_keys LIMIT :offset, :limit';
    }

    public function queryInsertPlace(): string
    {
        return 'INSERT INTO '.$this->prefix.'place (
                    '.implode(',', array_keys(Constants::FIELDS_FOR_PLACE)).'
                ) VALUES (
                    :'.implode(', :', array_keys(Constants::FIELDS_FOR_PLACE)).'
                )';
    }

    public function queryInsertAddress(): string
    {
        return 'INSERT INTO '.$this->prefix.'address (
                    '.implode(',', array_keys(Constants::FIELDS_FOR_ADDRESS)).'
                ) VALUES (
                    :'.implode(', :', array_keys(Constants::FIELDS_FOR_ADDRESS)).'
                )';
    }

    public function queryInsertAdminLevel(): string
    {
        return 'INSERT INTO '.$this->prefix.'admin_level (
                    '.implode(',', array_keys(Constants::FIELDS_FOR_ADMIN_LEVEL)).'
                ) VALUES (
                    :'.implode(', :', array_keys(Constants::FIELDS_FOR_ADMIN_LEVEL)).'
                )';
    }

    public function queryInsertSearchKey(): string
    {
        return 'INSERT INTO '.$this->prefix.'actual_keys (
                    '.implode(',', array_keys(Constants::FIELDS_FOR_ACTUAL_KEYS)).'
                ) VALUES (
                    :'.implode(', :', array_keys(Constants::FIELDS_FOR_ACTUAL_KEYS)).'
                )';
    }

    public function queryInsertPolygon(): string
    {
        return 'INSERT INTO '.$this->prefix.'polygon(
                    '.implode(',', array_keys(Constants::FIELDS_FOR_POLYGON)).'
                ) VALUES(
                    :'.implode(', :', array_keys(Constants::FIELDS_FOR_POLYGON)).'
                )';
    }

    public function querySelectSpecificPlace(): string
    {
        return 'SELECT '.implode(', ', array_keys(Constants::FIELDS_FOR_PLACE)).
            ' FROM '.$this->prefix.'place WHERE '.Constants::OBJECT_HASH.' = :'.Constants::OBJECT_HASH.' LIMIT 1';
    }

    public function querySelectAddresses(): string
    {
        return 'SELECT '.implode(', ', array_keys(Constants::FIELDS_FOR_ADDRESS)).
            ' FROM '.$this->prefix.'address WHERE '.Constants::OBJECT_HASH.' = :'.Constants::OBJECT_HASH;
    }

    public function querySelectAdminLevel(): string
    {
        return 'SELECT '.implode(', ', array_keys(Constants::FIELDS_FOR_ADMIN_LEVEL)).
            ' FROM '.$this->prefix.'admin_level WHERE '.Constants::OBJECT_HASH.' = :'.Constants::OBJECT_HASH.
            ' AND locale = :'.Constants::LOCALE;
    }

    public function querySelectPolygonPoints(): string
    {
        return 'SELECT '.implode(', ', array_keys(Constants::FIELDS_FOR_POLYGON)).
            ' FROM '.$this->prefix.'polygon WHERE '.Constants::OBJECT_HASH.' = :'.Constants::OBJECT_HASH.
            ' ORDER BY '.Constants::POLYGON_NUMBER.' ASC, '.Constants::POINT_NUMBER.
            ' ASC LIMIT :offset, 1000';
    }

    public function queryDelete(string $table): string
    {
        return 'DELETE FROM '.$this->prefix.$table.' WHERE '.Constants::OBJECT_HASH.' = :'.Constants::OBJECT_HASH;
    }
}
