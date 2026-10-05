<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IPGeolocation;

use Geocoder\Collection;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;

/**
 * @see https://ipgeolocation.io/documentation/ip-location-api.html
 */
final class IPGeolocation extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    public const GEOCODE_ENDPOINT_URL = 'https://api.ipgeolocation.io/v3/ipgeo';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param ClientInterface $client an HTTP adapter
     * @param string          $apiKey an IPGeolocation.io API key
     */
    public function __construct(ClientInterface $client, string $apiKey)
    {
        if (empty($apiKey)) {
            throw new InvalidCredentials('No API key provided.');
        }

        $this->apiKey = $apiKey;
        parent::__construct($client);
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();

        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The IPGeolocation provider does not support street addresses, only IP addresses.');
        }

        if (in_array($address, ['127.0.0.1', '::1'], true)) {
            return new AddressCollection([$this->getLocationForLocalhost()]);
        }

        $params = [
            'apiKey' => $this->apiKey,
            'ip' => $address,
            'fields' => 'location,time_zone.name',
        ];

        if (null !== $query->getLocale()) {
            $params['lang'] = $query->getLocale();
        }

        return $this->executeQuery(self::GEOCODE_ENDPOINT_URL.'?'.http_build_query($params));
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        throw new UnsupportedOperation('The IPGeolocation provider is not able to do reverse geocoding.');
    }

    public function getName(): string
    {
        return 'ipgeolocation';
    }

    private function executeQuery(string $url): AddressCollection
    {
        $response = $this->getHttpClient()->sendRequest($this->getRequest($url));
        $statusCode = $response->getStatusCode();

        // 404: the IP is not in the database, 423: the IP is private or reserved
        if (404 === $statusCode || 423 === $statusCode) {
            return new AddressCollection([]);
        }

        if (401 === $statusCode || 403 === $statusCode) {
            throw new InvalidCredentials();
        } elseif (429 === $statusCode) {
            throw new QuotaExceeded();
        } elseif ($statusCode >= 300) {
            throw InvalidServerResponse::create($url, $statusCode);
        }

        $body = (string) $response->getBody();
        if ('' === $body) {
            throw InvalidServerResponse::emptyResponse($url);
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw InvalidServerResponse::create($url, $statusCode);
        }

        if (!isset($data['location']) || !is_array($data['location'])) {
            return new AddressCollection([]);
        }

        $location = array_map(fn ($value) => '' === $value ? null : $value, $data['location']);

        $adminLevels = [];
        if (isset($location['state_prov'])) {
            $adminLevels[] = [
                'name' => $location['state_prov'],
                'code' => $location['state_code'] ?? null,
                'level' => 1,
            ];
        }
        if (isset($location['district'])) {
            $adminLevels[] = ['name' => $location['district'], 'level' => 2];
        }

        return new AddressCollection([
            Address::createFromArray([
                'providedBy' => $this->getName(),
                'latitude' => isset($location['latitude']) ? (float) $location['latitude'] : null,
                'longitude' => isset($location['longitude']) ? (float) $location['longitude'] : null,
                'locality' => $location['city'] ?? null,
                'postalCode' => $location['zipcode'] ?? null,
                'adminLevels' => $adminLevels,
                'country' => $location['country_name'] ?? null,
                'countryCode' => $location['country_code2'] ?? null,
                'timezone' => $data['time_zone']['name'] ?? null,
            ]),
        ]);
    }
}
