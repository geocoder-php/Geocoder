<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\StorageLocation\Tests\DataBase;

use Geocoder\Provider\StorageLocation\DataBase\PsrCache;
use Geocoder\Provider\StorageLocation\Model\DBConfig;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
class PsrCacheTest extends StorageLocationProviderIntegrationDbTest
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->dataBase = new PsrCache(new FilesystemAdapter(), new DBConfig());
    }
}
