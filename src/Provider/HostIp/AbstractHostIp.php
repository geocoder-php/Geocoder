<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\HostIp;

use Geocoder\Collection;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

/**
 * @author William Durand <william.durand1@gmail.com>
 * @author Oleg Andreyev <oleg@andreyev.lv>
 */
abstract class AbstractHostIp extends AbstractHttpProvider implements Provider
{
    abstract protected function executeQuery(string $url): AddressCollection;

    abstract protected function getEndpointURL(): string;

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The '.get_class($this).' provider does not support Street addresses.');
        }

        // This API does not support IPv6
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedOperation('The HostIp provider does not support IPv6 addresses.');
        }

        if ('127.0.0.1' === $address) {
            return new AddressCollection([$this->getLocationForLocalhost()]);
        }

        $url = sprintf($this->getEndpointURL(), $address);

        return $this->executeQuery($url);
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        throw new UnsupportedOperation('The HostIp provider is not able to do reverse geocoding.');
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function isUnknownLocation(array $data): bool
    {
        return empty($data['lat'])
            && empty($data['lng'])
            && '(Unknown City?)' === $data['city']
            && '(Unknown Country?)' === $data['country_name'];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function isPrivateLocation(array $data): bool
    {
        return empty($data['lat'])
            && empty($data['lng'])
            && '(Private Address)' === $data['city']
            && '(Private Address)' === $data['country_name'];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function prepareAddressCollection(array $data): AddressCollection
    {
        // Return empty collection if address was not found
        if ($this->isUnknownLocation($data)) {
            return new AddressCollection([]);
        }

        // Return empty collection if address was not found
        if ($this->isPrivateLocation($data)) {
            return new AddressCollection([]);
        }

        return new AddressCollection([
            Address::createFromArray([
                'providedBy' => $this->getName(),
                'latitude' => $data['lat'] ?? null,
                'longitude' => $data['lng'] ?? null,
                'locality' => $data['city'],
                'country' => $data['country_name'],
                'countryCode' => $data['country_code'],
            ]),
        ]);
    }
}
