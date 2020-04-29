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
interface HelperInterface
{
    public function queryForCreateTables(): array;

    public function queryGetAllPlaces(): string;

    public function queryGetAllAdminLevels(): string;

    public function queryGetAllActualKeys(): string;

    public function queryInsertPlace(): string;

    public function queryInsertAddress(): string;

    public function queryInsertAdminLevel(): string;

    public function queryInsertSearchKey(): string;

    public function queryInsertPolygon(): string;

    public function querySelectSpecificPlace(): string;

    public function querySelectAddresses(): string;

    public function querySelectAdminLevel(): string;

    public function querySelectPolygonPoints(): string;

    public function queryDelete(string $table): string;
}
