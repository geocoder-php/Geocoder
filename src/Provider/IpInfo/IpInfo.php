<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IpInfo;

use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Collection;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;

/**
 * @author Roro Neutron <imprec@gmail.com>
 */
final class IpInfo extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'https://ipinfo.io/%s/json';

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();

        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The IpInfo provider does not support street addresses, only IP addresses.');
        }

        if (in_array($address, ['127.0.0.1', '::1'], true)) {
            return new AddressCollection([$this->getLocationForLocalhost()]);
        }

        return $this->executeQuery(sprintf(self::ENDPOINT_URL, $address));
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        throw new UnsupportedOperation('The IpInfo provider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'ip_info';
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

        if (empty($data) || !isset($data['loc']) || '' === $data['loc']) {
            return new AddressCollection([]);
        }

        $location = explode(',', $data['loc']);

        return new AddressCollection([
            Address::createFromArray([
                'providedBy' => $this->getName(),
                'latitude' => $location[0],
                'longitude' => $location[1],
                'locality' => $data['city'] ?? null,
                'postalCode' => $data['postal'] ?? null,
                'adminLevels' => isset($data['region']) ? [['name' => $data['region'], 'level' => 1]] : [],
                'countryCode' => $data['country'] ?? null,
            ]),
        ]);
    }
}
