<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Ipstack;

use Geocoder\Collection;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Psr\Http\Client\ClientInterface;

/**
 * @author Jonas Gielen <gielenjonas@gmail.com>
 */
final class Ipstack extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://api.ipstack.com/%s?access_key=%s';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param ClientInterface $client an HTTP adapter
     * @param string          $apiKey an API key
     */
    public function __construct(ClientInterface $client, string $apiKey)
    {
        if (empty($apiKey)) {
            throw new InvalidCredentials('No API key provided.');
        }

        $this->apiKey = $apiKey;
        parent::__construct($client);
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();

        // This API doesn't handle IPs
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Ipstack provider does not support street addresses.');
        }

        if (in_array($address, ['127.0.0.1', '::1'])) {
            return new AddressCollection([$this->getLocationForLocalhost()]);
        }

        $url = sprintf(sprintf(self::GEOCODE_ENDPOINT_URL, $address, $this->apiKey));

        if (null !== $query->getLocale()) {
            $url = sprintf('%s&language=%s', $url, $query->getLocale());
        }

        $body = $this->getUrlContents($url);
        $data = json_decode($body, true);

        // https://ipstack.com/documentation#errors
        if (isset($data['error'])) {
            switch ($data['error']['code']) {
                case 301:
                    throw new InvalidArgument('Invalid request (a required parameter is missing).');
                case 303:
                    throw new InvalidArgument('Bulk requests are not supported on your plan. Please upgrade your subscription.');
                case 104:
                    throw new QuotaExceeded('The maximum allowed amount of monthly API requests has been reached.');
                case 101:
                    throw new InvalidCredentials('No API Key was specified or an invalid API Key was specified.');
            }
        }

        if (null === $data['latitude']
            && null === $data['longitude']
            && null === $data['city']
            && null === $data['zip']
            && null === $data['country_name']
            && null === $data['country_code']) {
            return new AddressCollection([]);
        }

        $locations[] = Address::createFromArray([
            'providedBy' => $this->getName(),
            'latitude' => $data['latitude'] ?: null,
            'longitude' => $data['longitude'] ?: null,
            'locality' => $data['city'] ?: null,
            'postalCode' => $data['zip'] ?: null,
            'country' => $data['country_name'] ?: null,
            'countryCode' => $data['country_code'] ?: null,
        ]);

        return new AddressCollection($locations);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        throw new UnsupportedOperation('The Ipstack provider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'ipstack';
    }
}
