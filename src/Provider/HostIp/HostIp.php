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

use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Collection;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class HostIp extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://api.hostip.info/get_json.php?ip=%s&position=true';

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The HostIp provider does not support Street addresses.');
        }

        // This API does not support IPv6
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedOperation('The HostIp provider does not support IPv6 addresses.');
        }

        if ('127.0.0.1' === $address) {
            return new AddressCollection([$this->getLocationForLocalhost()]);
        }

        $url = sprintf(self::ENDPOINT_URL, $address);

        return $this->executeQuery($url);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        throw new UnsupportedOperation('The HostIp provider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'host_ip';
    }

    /**
     * @param string $url
     *
     * @return Collection
     */
    private function executeQuery(string $url): AddressCollection
    {
        $content = $this->getUrlContents($url);
        $data = json_decode($content, true);

        if (!$data) {
            return new AddressCollection([]);
        }

        // Return empty collection if address was not found
        if (null === $data['lat']
        && null === $data['lng']
        && '(Unknown City?)' === $data['city']
        && '(Unknown Country?)' === $data['country_name']
        && 'XX' === $data['country_code']) {
            return new AddressCollection([]);
        }

        // Return empty collection if address was not found
        if (null === $data['lat']
        && null === $data['lng']
        && '(Private Address)' === $data['city']
        && '(Private Address)' === $data['country_name']
        && 'XX' === $data['country_code']) {
            return new AddressCollection([]);
        }

        return new AddressCollection([
            Address::createFromArray([
                'providedBy' => $this->getName(),
                'latitude' => $data['lat'],
                'longitude' => $data['lng'],
                'locality' => $data['city'],
                'country' => $data['country_name'],
                'countryCode' => $data['country_code'],
            ]),
        ]);
    }
}
