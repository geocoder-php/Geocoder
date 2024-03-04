<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Pelias;

use Geocoder\Collection;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Pelias\Model\PeliasAddress;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;

class Pelias extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    protected $root;

    /**
     * @param ClientInterface $client  an HTTP adapter
     * @param string          $root    url of Pelias API
     * @param int             $version version of Pelias API
     */
    public function __construct(ClientInterface $client, string $root, int $version = 1)
    {
        $this->root = sprintf('%s/v%d', rtrim($root, '/'), $version);

        parent::__construct($client);
    }

    /**
     * @param array<string, mixed> $query_data additional query data (API key for instance)
     *
     * @throws \Geocoder\Exception\Exception
     */
    protected function getGeocodeQueryUrl(GeocodeQuery $query, array $query_data = []): string
    {
        $address = $query->getText();

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation(sprintf('The %s provider does not support IP addresses, only street addresses.', $this->getName()));
        }

        $data = [
            'text' => $address,
            'size' => $query->getLimit(),
        ];

        return sprintf('%s/search?%s', $this->root, http_build_query(array_merge($data, $query_data)));
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        return $this->executeQuery($this->getGeocodeQueryUrl($query));
    }

    /**
     * @param array<string, mixed> $query_data additional query data (API key for instance)
     *
     * @throws \Geocoder\Exception\Exception
     */
    protected function getReverseQueryUrl(ReverseQuery $query, array $query_data = []): string
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();

        $data = [
            'point.lat' => $latitude,
            'point.lon' => $longitude,
            'size' => $query->getLimit(),
        ];

        return sprintf('%s/reverse?%s', $this->root, http_build_query(array_merge($data, $query_data)));
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        return $this->executeQuery($this->getReverseQueryUrl($query));
    }

    public function getName(): string
    {
        return 'pelias';
    }

    protected function executeQuery(string $url): AddressCollection
    {
        $content = $this->getUrlContents($url);
        $json = json_decode($content, true);

        if (isset($json['meta'])) {
            switch ($json['meta']['status_code']) {
                case 401:
                case 403:
                    throw new InvalidCredentials('Invalid or missing api key.');
                case 429:
                    throw new QuotaExceeded('Valid request but quota exceeded.');
            }
        }

        if (
            !isset($json['type'])
            || 'FeatureCollection' !== $json['type']
            || !isset($json['features'])
            || [] === $json['features']
        ) {
            return new AddressCollection([]);
        }

        $locations = $json['features'];

        if (empty($locations)) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($locations as $location) {
            $results[] = $this->buildAddress($location);
        }

        return new AddressCollection($results);
    }

    /**
     * Build the Address object from the the Feature.
     *
     * @param array<mixed> $location the Feature array
     *
     * @return PeliasAddress the address object
     */
    protected function buildAddress(array $location): PeliasAddress
    {
        $bounds = [
            'south' => $location['bbox'][3] ?? null,
            'west' => $location['bbox'][2] ?? null,
            'north' => $location['bbox'][1] ?? null,
            'east' => $location['bbox'][0] ?? null,
        ];

        $props = $location['properties'];
        $adminLevels = [];
        foreach (['region', 'county', 'locality', 'macroregion', 'country'] as $i => $component) {
            if (isset($props[$component])) {
                $adminLevels[] = ['name' => $props[$component], 'level' => $i + 1];
            }
        }

        return PeliasAddress::createFromArray([
            'providedBy' => $this->getName(),
            'latitude' => $location['geometry']['coordinates'][1],
            'longitude' => $location['geometry']['coordinates'][0],
            'bounds' => $bounds,
            'streetNumber' => $props['housenumber'] ?? null,
            'streetName' => $props['street'] ?? null,
            'subLocality' => $props['neighbourhood'] ?? null,
            'locality' => $props['locality'] ?? null,
            'postalCode' => $props['postalcode'] ?? null,
            'adminLevels' => $adminLevels,
            'country' => $props['country'] ?? null,
            'countryCode' => isset($props['country_a']) ? strtoupper($props['country_a']) : null,
            'layer' => $props['layer'] ?? null,
            'confidence' => $props['confidence'] ?? null,
            'match_type' => $props['match_type'] ?? null,
            'source' => $props['source'] ?? null,
            'accuracy' => $props['accuracy'] ?? null,
        ]);
    }

    /**
     * @param array<string, mixed> $components
     *
     * @return string|null
     */
    protected function guessLocality(array $components)
    {
        $localityKeys = ['city', 'town', 'village', 'hamlet'];

        return $this->guessBestComponent($components, $localityKeys);
    }

    /**
     * @param array<string, mixed> $components
     *
     * @return string|null
     */
    protected function guessStreetName(array $components)
    {
        $streetNameKeys = ['road', 'street', 'street_name', 'residential'];

        return $this->guessBestComponent($components, $streetNameKeys);
    }

    /**
     * @param array<string, mixed> $components
     *
     * @return string|null
     */
    protected function guessSubLocality(array $components)
    {
        $subLocalityKeys = ['neighbourhood', 'city_district'];

        return $this->guessBestComponent($components, $subLocalityKeys);
    }

    /**
     * @param array<string, mixed> $components
     * @param string[]             $keys
     *
     * @return string|null
     */
    protected function guessBestComponent(array $components, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($components[$key]) && !empty($components[$key])) {
                return $components[$key];
            }
        }

        return null;
    }
}
