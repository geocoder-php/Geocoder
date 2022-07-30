<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Mapzen;

use Geocoder\Collection;
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
 * Mapzen has shut down as their APIs as of February 1, 2018.
 *
 * @deprecated https://github.com/geocoder-php/Geocoder/issues/808
 */
final class Mapzen extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'https://search.mapzen.com/v1/search?text=%s&api_key=%s&size=%d';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'https://search.mapzen.com/v1/reverse?point.lat=%f&point.lon=%f&api_key=%s&size=%d';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * Mapzen has shut down as their APIs as of February 1, 2018.
     *
     * @deprecated https://github.com/geocoder-php/Geocoder/issues/808
     *
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
            throw new UnsupportedOperation('The Mapzen provider does not support IP addresses, only street addresses.');
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address), $this->apiKey, $query->getLimit());

        return $this->executeQuery($url);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();
        $url = sprintf(self::REVERSE_ENDPOINT_URL, $latitude, $longitude, $this->apiKey, $query->getLimit());

        return $this->executeQuery($url);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'mapzen';
    }

    /**
     * @param $url
     *
     * @return Collection
     */
    private function executeQuery(string $url): AddressCollection
    {
        $content = $this->getUrlContents($url);
        $json = json_decode($content, true);

        // See https://mapzen.com/documentation/search/api-keys-rate-limits/
        if (isset($json['meta'])) {
            switch ($json['meta']['status_code']) {
                case 403:
                    throw new InvalidCredentials('Invalid or missing api key.');
                case 429:
                    throw new QuotaExceeded('Valid request but quota exceeded.');
            }
        }

        if (!isset($json['type']) || 'FeatureCollection' !== $json['type'] || !isset($json['features']) || 0 === count($json['features'])) {
            return new AddressCollection([]);
        }

        $locations = $json['features'];

        if (empty($locations)) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($locations as $location) {
            $bounds = [
                'south' => null,
                'west' => null,
                'north' => null,
                'east' => null,
            ];
            if (isset($location['bbox'])) {
                $bounds = [
                    'south' => $location['bbox'][3],
                    'west' => $location['bbox'][2],
                    'north' => $location['bbox'][1],
                    'east' => $location['bbox'][0],
                ];
            }

            $props = $location['properties'];

            $adminLevels = [];
            foreach (['region', 'county', 'locality', 'macroregion', 'country'] as $i => $component) {
                if (isset($props[$component])) {
                    $adminLevels[] = ['name' => $props[$component], 'level' => $i + 1];
                }
            }

            $results[] = Address::createFromArray([
                'providedBy' => $this->getName(),
                'latitude' => $location['geometry']['coordinates'][1],
                'longitude' => $location['geometry']['coordinates'][0],
                'bounds' => $bounds,
                'streetNumber' => isset($props['housenumber']) ? $props['housenumber'] : null,
                'streetName' => isset($props['street']) ? $props['street'] : null,
                'subLocality' => isset($props['neighbourhood']) ? $props['neighbourhood'] : null,
                'locality' => isset($props['locality']) ? $props['locality'] : null,
                'postalCode' => isset($props['postalcode']) ? $props['postalcode'] : null,
                'adminLevels' => $adminLevels,
                'country' => isset($props['country']) ? $props['country'] : null,
                'countryCode' => isset($props['country_a']) ? strtoupper($props['country_a']) : null,
            ]);
        }

        return new AddressCollection($results);
    }

    /**
     * @param array $components
     *
     * @return string|null
     */
    protected function guessLocality(array $components)
    {
        $localityKeys = ['city', 'town', 'village', 'hamlet'];

        return $this->guessBestComponent($components, $localityKeys);
    }

    /**
     * @param array $components
     *
     * @return string|null
     */
    protected function guessStreetName(array $components)
    {
        $streetNameKeys = ['road', 'street', 'street_name', 'residential'];

        return $this->guessBestComponent($components, $streetNameKeys);
    }

    /**
     * @param array $components
     *
     * @return string|null
     */
    protected function guessSubLocality(array $components)
    {
        $subLocalityKeys = ['neighbourhood', 'city_district'];

        return $this->guessBestComponent($components, $subLocalityKeys);
    }

    /**
     * @param array $components
     * @param array $keys
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
