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

use Geocoder\Provider\StorageLocation\Model\DBConfig;

/**
 * @author Borys Yermokhin <borys_ermokhin@yahoo.com>
 */
class HelperLocator
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var DBConfig
     */
    private $dbConfig;

    public function __construct(\PDO $pdo, DBConfig $dbConfig)
    {
        $this->pdo = $pdo;
        $this->dbConfig = $dbConfig;
    }

    /**
     * @see SqliteHelper
     * @see MysqlHelper
     * @see PostgresqlHelper
     *
     * @return HelperInterface
     *
     * @throws \Exception
     */
    public function getHelper(): HelperInterface
    {
        $alias = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $className = __NAMESPACE__.'\\'.ucfirst($alias).'Helper';

        if (!class_exists($className)) {
            throw new \Exception('Can\'t find class '.$className);
        }

        $helper = new $className(implode($this->dbConfig->getGlueForSections(), $this->dbConfig->getGlobalPrefix()));

        if (!isset(class_implements($helper)[HelperInterface::class])) {
            throw new \Exception($className.' not implement '.HelperInterface::class);
        }

        return $helper;
    }
}
