<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\StorageLocation\Tests\Database;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
trait PdoDatabaseTrait
{
    public function generateTempDbFile(): string
    {
        $dbFileName = sys_get_temp_dir().DIRECTORY_SEPARATOR.'test_sqlite.db';
        $fp = fopen($dbFileName, 'w');
        fclose($fp);
        chmod($dbFileName, 0777);

        return $dbFileName;
    }

    public function generatePdo(string $dbFileName): \PDO
    {
        $pdo = new \PDO('sqlite:'.$dbFileName);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}
