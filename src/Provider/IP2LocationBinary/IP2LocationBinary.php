<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IP2LocationBinary;

use Geocoder\Collection;
use Geocoder\Exception\FunctionNotFound;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\AbstractProvider;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

final class IP2LocationBinary extends AbstractProvider implements Provider
{
    /**
     * @var string
     */
    private $binFile;

    /**
     * @var int|null
     */
    private $openFlag;

    /**
     * @throws FunctionNotFound if IP2Location's library not installed
     * @throws InvalidArgument  if dat file is not correct (optional)
     */
    public function __construct(string $binFile, ?int $openFlag = null)
    {
        if (false === class_exists('\\IP2Location\\Database')) {
            throw new FunctionNotFound('ip2location_database', 'The IP2LocationBinary requires IP2Location\'s library to be installed and loaded.');
        }

        if (false === is_file($binFile)) {
            throw new InvalidArgument(sprintf('Given IP2Location BIN file "%s" does not exist.', $binFile));
        }

        if (false === is_readable($binFile)) {
            throw new InvalidArgument(sprintf('Given IP2Location BIN file "%s" does not readable.', $binFile));
        }

        $this->binFile = $binFile;
        $this->openFlag = null === $openFlag ? \IP2Location\Database::FILE_IO : $openFlag;
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();
        if (false === filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The IP2LocationBinary provider does not support street addresses.');
        }

        $db = new \IP2Location\Database($this->binFile, $this->openFlag);
        $records = $db->lookup($address, \IP2Location\Database::ALL);

        if (false === $records) {
            return new AddressCollection([]);
        }

        $adminLevels = [];

        if (isset($records['regionName'])) {
            $adminLevels[] = ['name' => $records['regionName'], 'level' => 1];
        }

        return new AddressCollection([
            Address::createFromArray([
                'providedBy' => $this->getName(),
                'countryCode' => $records['countryCode'],
                'country' => null === $records['countryName'] ? null : mb_convert_encoding($records['countryName'], 'UTF-8', 'ISO-8859-1'),
                'adminLevels' => $adminLevels,
                'locality' => null === $records['cityName'] ? null : mb_convert_encoding($records['cityName'], 'UTF-8', 'ISO-8859-1'),
                'latitude' => $records['latitude'],
                'longitude' => $records['longitude'],
                'postalCode' => $records['zipCode'],
            ]),
        ]);
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        throw new UnsupportedOperation('The IP2LocationBinary is not able to do reverse geocoding.');
    }

    public function getName(): string
    {
        return 'ip2location_binary';
    }
}
