<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\HttpAdapter;

use Geocoder\Exception\RuntimeException;
use Geocoder\Exception\InvalidArgumentException;
use Geocoder\Exception\UnsupportedException;
use GeoIp2\Database\Reader;

/**
 * @author Jens Wiese <jens@howtrueisfalse.de>
 */
class GeoIP2DatabaseAdapter implements HttpAdapterInterface
{
    /**
     * Database file types
     */
    const GEOIP2_CITY    = 'geoip2_city';
    const GEOIP2_COUNTRY = 'geoip2_country';

    /**
     * @var string
     */
    protected $dbFile;

    /**
     * @var string
     */
    protected $dbType;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var Reader
     */
    protected $dbReader;

    /**
     * @param string $dbFile
     * @param string $dbType (e.g. self::GEOIP2_CITY)
     * @throws \Geocoder\Exception\RuntimeException
     * @throws \Geocoder\Exception\InvalidArgumentException
     */
    public function __construct($dbFile, $dbType = self::GEOIP2_CITY)
    {
        if (false === class_exists('\GeoIp2\Database\Reader')) {
            throw new RuntimeException(sprintf("The %s requires maxmind's lib 'geoip2/geoip2'", __CLASS__));
        }

        if (false === is_file($dbFile)) {
            throw new InvalidArgumentException(sprintf('Given MaxMind database file "%s" is not a file.', $dbFile));
        }

        if (false === is_readable($dbFile)) {
            throw new InvalidArgumentException(sprintf('Given MaxMind database file "%s" is not readable.', $dbFile));
        }

        $this->dbFile = $dbFile;
        $this->dbType = $dbType;
    }

    /**
     * @param Reader $dbReader
     */
    public function setDbReader(Reader $dbReader)
    {
        $this->dbReader = $dbReader;
    }

    /**
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Destruct (e.g. database reader)
     */
    public function __destruct()
    {
        $this->getDbReader()->close();
    }

    /**
     * Returns the content fetched from a given resource.
     *
     * @param string $url (e.g. file://database?127.0.0.1)
     * @throws \Geocoder\Exception\UnsupportedException
     * @throws \Geocoder\Exception\InvalidArgumentException
     * @return string
     */
    public function getContent($url)
    {
        $url = trim($url);

        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(
                sprintf('"%s" must be called with a valid url. Got "%s" instead.', __METHOD__, $url)
            );
        }

        $ipAddress = parse_url($url, PHP_URL_QUERY);

        if (false === filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('URL must contain a valid query-string (a IP address, 127.0.0.1 for instance)');
        }

        switch ($this->dbType) {
            case self::GEOIP2_CITY:
                $result = $this->getDbReader()->city($ipAddress);
                break;
            default:
                throw new UnsupportedException(
                    sprintf('Database type "%s" not implemented yet.', $this->dbType)
                );
        }

        return json_encode($result);
    }

    /**
     * Returns the name of the Adapter.
     *
     * @return string
     */
    public function getName()
    {
        return 'maxmind_database';
    }

    /**
     * Returns database reader
     *
     * @return Reader
     */
    protected function getDbReader()
    {
        if (is_null($this->dbReader)) {
            $this->dbReader = new Reader($this->dbFile, $this->getLocale());
        }

        return $this->dbReader;
    }
}