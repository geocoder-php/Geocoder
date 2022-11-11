<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\TomTom;

use Geocoder\Collection;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Psr\Http\Client\ClientInterface;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
final class TomTom extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'https://api.tomtom.com/search/2/geocode/%s.json?key=%s&limit=%d';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'https://api.tomtom.com/search/2/reverseGeocode/%F,%F.json?key=%s';

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
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The TomTom provider does not support IP addresses, only street addresses.');
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL, rawurlencode($address), $this->apiKey, $query->getLimit());

        if (null !== $query->getLocale()) {
            $url = sprintf('%s&language=%s', $url, $query->getLocale());
        }

        if (null !== $query->getData('countrySet')) {
            $url = sprintf('%s&countrySet=%s', $url, $query->getData('countrySet'));
        }

        $content = $this->getUrlContents($url);
        if (false !== stripos($content, 'Developer Inactive')) {
            throw new InvalidCredentials('Map API Key provided is not valid.');
        }

        $json = json_decode($content, true);
        if (!isset($json['results']) || empty($json['results'])) {
            return new AddressCollection([]);
        }

        $locations = [];
        foreach ($json['results'] as $item) {
            $locations[] = Address::createFromArray([
                'providedBy' => $this->getName(),
                'latitude' => $item['position']['lat'] ?? null,
                'longitude' => $item['position']['lon'] ?? null,
                'streetName' => $item['address']['streetName'] ?? null,
                'streetNumber' => $item['address']['streetNumber'] ?? null,
                'locality' => $item['address']['municipality'] ?? null,
                'subLocality' => $item['address']['municipalitySubdivision'] ?? null,
                'postalCode' => $item['address']['postalCode'] ?? null,
                'country' => $item['address']['country'] ?? null,
                'countryCode' => $item['address']['countryCode'] ?? null,
            ]);
        }

        return new AddressCollection($locations);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();

        $url = sprintf(self::REVERSE_ENDPOINT_URL, $latitude, $longitude, $this->apiKey);

        $content = $this->getUrlContents($url);
        if (false !== stripos($content, 'Developer Inactive')) {
            throw new InvalidCredentials('Map API Key provided is not valid.');
        }

        $json = json_decode($content, true);

        if (!isset($json['addresses']) || [] === $json['addresses']) {
            return new AddressCollection([]);
        }

        $results = $json['addresses'];

        $locations = [];
        foreach ($results as $item) {
            list($lat, $lon) = explode(',', $item['position']);
            $locations[] = Address::createFromArray([
                'providedBy' => $this->getName(),
                'latitude' => $lat,
                'longitude' => $lon,
                'streetName' => $item['address']['streetName'] ?? null,
                'streetNumber' => $item['address']['streetNumber'] ?? null,
                'locality' => $item['address']['municipality'] ?? null,
                'subLocality' => $item['address']['municipalitySubdivision'] ?? null,
                'postalCode' => $item['address']['postalCode'] ?? null,
                'country' => $item['address']['country'] ?? null,
                'countryCode' => $item['address']['countryCode'] ?? null,
            ]);
        }

        return new AddressCollection($locations);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'tomtom';
    }
}
