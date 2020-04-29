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

use Geocoder\Provider\StorageLocation\Database\PdoDatabase;
use Geocoder\Provider\StorageLocation\Model\DBConfig;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
class PdoDatabaseCompressedTest extends StorageLocationProviderIntegrationDbTest
{
    use PdoDatabaseTrait;

    private $dbFileName;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->dbFileName = $this->generateTempDbFile();

        $dbConfig = new DBConfig();
        $dbConfig->setUseCompression(true);
        $dbConfig->setCompressionLevel(1);

        parent::__construct($name, $data, $dataName);
        $this->dataBase = new PdoDatabase($this->generatePdo($this->dbFileName), $dbConfig);
    }

    public function __destruct()
    {
        if (is_file($this->dbFileName)) {
            unlink($this->dbFileName);
        }
    }
}
