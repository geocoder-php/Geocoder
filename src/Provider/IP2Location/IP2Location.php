<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IP2Location;

use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Collection;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Psr\Http\Client\ClientInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class IP2Location extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'https://api.ip2location.com/v2/?key=%s&ip=%s&format=json&package=WS9';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $endpointUrl;

    /**
     * @param ClientInterface $client a HTTP adapter
     * @param string          $apiKey an API key
     */
    public function __construct(ClientInterface $client, string $apiKey)
    {
        parent::__construct($client);

        $this->apiKey = $apiKey;
        $this->endpointUrl = self::ENDPOINT_URL;
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();

        if (empty($this->apiKey)) {
            throw new InvalidCredentials('No API key provided.');
        }

        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The IP2Location provider does not support street addresses, only IP addresses.');
        }

        if (in_array($address, ['127.0.0.1', '::1'])) {
            return new AddressCollection([$this->getLocationForLocalhost()]);
        }

        $url = sprintf($this->endpointUrl, $this->apiKey, $address);

        return $this->executeQuery($url);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        throw new UnsupportedOperation('The IP2Location provider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'ip2location';
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

        if (empty($data)) {
            return new AddressCollection([]);
        }

        if (isset($data['response'])) {
            if (preg_match('/suspended|denied|invalid account/i', $data['response'])) {
                throw new InvalidCredentials('API Key provided is not valid.');
            } elseif (preg_match('/insufficient/i', $data['response'])) {
                throw new InvalidCredentials('Insufficient credits to use IP2Location service.');
            } elseif (preg_match('/invalid ip address/i', $data['response'])) {
                throw new UnsupportedOperation('Invalid IP address.');
            } else {
                throw new UnsupportedOperation('Unexpected error.');
            }
        }

        if (isset($data['region_name'])) {
            $adminLevels = [[
                'name' => $data['region_name'],
                'level' => 1,
            ]];
        } else {
            $adminLevels = [];
        }

        return new AddressCollection([
            Address::createFromArray([
                'providedBy' => $this->getName(),
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'locality' => $data['city_name'] ?? null,
                'postalCode' => $data['zip_code'] ?? null,
                'adminLevels' => $adminLevels,
                'country' => $data['country_name'] ?? null,
                'countryCode' => $data['country_code'] ?? null,
            ]),
        ]);
    }
}
