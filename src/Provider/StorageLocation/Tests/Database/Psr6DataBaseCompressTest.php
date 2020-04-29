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

use Cache\Adapter\PHPArray\ArrayCachePool;
use Geocoder\Provider\StorageLocation\Database\Psr6Database;
use Geocoder\Provider\StorageLocation\Model\DBConfig;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
class Psr6DataBaseCompressTest extends StorageLocationProviderIntegrationDbTest
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $dbConfig = new DBConfig();
        $dbConfig->setUseCompression(true);
        $dbConfig->setCompressionLevel(1);

        $this->dataBase = new Psr6Database(new ArrayCachePool(), $dbConfig);
    }
}
