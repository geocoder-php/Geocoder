<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\IpInfoDb;

use Geocoder\Exception\InvalidArgument;
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
final class IpInfoDb extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const CITY_PRECISION_ENDPOINT_URL = 'https://api.ipinfodb.com/v3/ip-city/?key=%s&format=json&ip=%s';

    /**
     * @var string
     */
    const COUNTRY_PRECISION_ENDPOINT_URL = 'https://api.ipinfodb.com/v3/ip-country/?key=%s&format=json&ip=%s';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $endpointUrl;

    /**
     * @param ClientInterface $client    an HTTP adapter
     * @param string          $apiKey    an API key
     * @param string          $precision The endpoint precision. Either "city" or "country" (faster)
     *
     * @throws \Geocoder\Exception\InvalidArgument
     */
    public function __construct(ClientInterface $client, string $apiKey, string $precision = 'city')
    {
        parent::__construct($client);

        $this->apiKey = $apiKey;
        switch ($precision) {
            case 'city':
                $this->endpointUrl = self::CITY_PRECISION_ENDPOINT_URL;

                break;

            case 'country':
                $this->endpointUrl = self::COUNTRY_PRECISION_ENDPOINT_URL;

                break;

            default:
                throw new InvalidArgument(sprintf('Invalid precision value "%s" (allowed values: "city", "country").', $precision));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided.');
        }

        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The IpInfoDb provider does not support street addresses, only IPv4 addresses.');
        }

        // This API does not support IPv6
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedOperation('The IpInfoDb provider does not support IPv6 addresses, only IPv4 addresses.');
        }

        if ('127.0.0.1' === $address) {
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
        throw new UnsupportedOperation('The IpInfoDb provider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'ip_info_db';
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

        if (empty($data) || 'OK' !== $data['statusCode']) {
            return new AddressCollection([]);
        }

        $timezone = null;
        if (isset($data['timeZone'])) {
            $timezone = timezone_name_from_abbr('', (int) substr($data['timeZone'], 0, strpos($data['timeZone'], ':')) * 3600, 0);
        }

        return new AddressCollection([
            Address::createFromArray([
                'providedBy' => $this->getName(),
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'locality' => $data['cityName'] ?? null,
                'postalCode' => $data['zipCode'] ?? null,
                'adminLevels' => isset($data['regionName']) ? [['name' => $data['regionName'], 'level' => 1]] : [],
                'country' => $data['countryName'] ?? null,
                'countryCode' => $data['countryCode'] ?? null,
                'timezone' => $timezone,
            ]),
        ]);
    }
}
