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
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;
use function array_merge;
use function count;
use function filter_var;
use function http_build_query;
use function json_decode;
use function rtrim;
use function sprintf;
use function strtoupper;

class Pelias extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    protected $root;

    /**
     * @var int
     */
    private $version;

    /**
     * @param HttpClient $client  an HTTP adapter
     * @param string     $root    url of Pelias API
     * @param int        $version version of Pelias API
     */
    public function __construct(HttpClient $client, string $root, int $version = 1)
    {
        $this->root = sprintf('%s/v%d', rtrim($root, '/'), $version);
        $this->version = $version;

        parent::__construct($client);
    }

    /**
     * @param GeocodeQuery $query
     * @param array        $query_data additional query data (API key for instance)
     *
     * @return string
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
            'lang' => $query->getLocale() ?? 'en',
        ];

        return sprintf('%s/search?%s', $this->root, http_build_query(array_merge($data, $query_data)));
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        return $this->executeQuery($this->getGeocodeQueryUrl($query));
    }

    /**
     * @param ReverseQuery $query
     * @param array        $query_data additional query data (API key for instance)
     *
     * @return string
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
            'lang' => $query->getLocale() ?? 'en',
        ];

        return sprintf('%s/reverse?%s', $this->root, http_build_query(array_merge($data, $query_data)));
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        return $this->executeQuery($this->getReverseQueryUrl($query));
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'pelias';
    }

    /**
     * @param $url
     *
     * @return Collection
     * @throws \JsonException
     */
    protected function executeQuery(string $url): AddressCollection
    {
        $content = $this->getUrlContents($url);
        $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

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
            if (isset($location['bbox'])) {
                $bounds = [
                    'south' => $location['bbox'][3],
                    'west' => $location['bbox'][2],
                    'north' => $location['bbox'][1],
                    'east' => $location['bbox'][0],
                ];
            } else {
                $bounds = [
                    'south' => null,
                    'west' => null,
                    'north' => null,
                    'east' => null,
                ];
            }

            $props = $location['properties'];

            $adminLevels = [];
            foreach (['region', 'county', 'locality', 'macroregion', 'country'] as $i => $component) {
                if (isset($props[$component])) {
                    $adminLevels[] = [
                        'name' => $props[$component],
                        'level' => $i + 1,
                        'code' => $props[sprintf('%s_a', $component)] ?? null,
                    ];
                }
            }

            $results[] = PeliasAddress
                ::createFromArray([
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
                ])
                ->withGID($props['gid'] ?? null)
                ->withSource($props['source'] ?? null);
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
