<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */
namespace Geocoder\Provider;

use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\NoResult;
use Geocoder\Exception\UnsupportedOperation;
use Http\Client\HttpClient;

/**
 * @author Gary Gale <gary@vicchi.org>
 */
final class Mapzen extends AbstractHttpProvider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = '%s://search.mapzen.com/v1/search?text=%s&key=%s&size=%d';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = '%s://search.mapzen.com/v1/reverse?point.lat=%f&point.lon=%f&key=%s&size=%d';

    /**
     * @var string
     */
    private $scheme;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param HttpClient  $client An HTTP adapter.
     * @param string      $apiKey An API key.
     * @param bool        $useSsl Whether to use an SSL connection (optional).
     */
    public function __construct(HttpClient $client, $apiKey, $useSSL = true)
    {
        parent::__construct($client);

        $this->apiKey = $apiKey;
        $this->scheme = $useSSL ? 'https' : 'http';
    }

    /**
     * {@inheritdoc}
     */
    public function geocode($address)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided.');
        }

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Mapzen provider does not support IP addresses, only street addresses.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $this->scheme, urlencode($address), $this->apiKey, $this->getLimit());

        return $this->executeQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function reverse($latitude, $longitude)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided.');
        }

        $query = sprintf(self::REVERSE_ENDPOINT_URL, $this->scheme, $latitude, $longitude, $this->apiKey, $this->getLimit());

        return $this->executeQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mapzen';
    }

    /**
     * @param $query
     * @return \Geocoder\Model\AddressCollection
     */
    private function executeQuery($query)
    {
        $request = $this->getMessageFactory()->createRequest('GET', $query);
        $content = (string) $this->getHttpClient()->sendRequest($request)->getBody();

        if (empty($content)) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

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

        if (!isset($json['type']) || $json['type'] !== 'FeatureCollection' || !isset($json['features']) || count($json['features']) === 0) {
            throw new NoResult(sprintf('Could not find results for query "%s".', $query));
        }

        $locations = $json['features'];

        if (empty($locations)) {
            throw new NoResult(sprintf('Could not find results for query "%s".', $query));
        }

        $results = [];
        foreach ($locations as $location) {
            $bounds = [];
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
            foreach (['region', 'locality', 'macroregion', 'country'] as $i => $component) {
                if (isset($props[$component])) {
                    $adminLevels[] = ['name' => $props[$component], 'level' => $i + 1];
                }
            }

            $results[] = array_merge($this->getDefaults(), array(
                'latitude' => $location['geometry']['coordinates'][1],
                'longitude' => $location['geometry']['coordinates'][0],
                'bounds' => $bounds ?: [],
                'streetNumber' => isset($props['housenumber']) ? $props['housenumber'] : null,
                'streetName' => isset($props['street']) ? $props['street'] : null,
                'subLocality' => isset($props['neighbourhood']) ? $props['neighbourhood'] : null,
                'locality' => isset($props['locality']) ? $props['locality'] : null,
                'postalCode' => isset($props['postalcode']) ? $props['postalcode'] : null,
                'adminLevels' => $adminLevels,
                'country' => isset($props['country']) ? $props['country'] : null,
                'countryCode' => isset($props['country_a']) ? strtoupper($props['country_a']) : null
            ));
        }

        return $this->returnResults($results);
    }

    /**
     * @param array $components
     *
     * @return null|string
     */
    protected function guessLocality(array $components)
    {
        $localityKeys = array('city', 'town' , 'village', 'hamlet');

        return $this->guessBestComponent($components, $localityKeys);
    }

    /**
     * @param array $components
     *
     * @return null|string
     */
    protected function guessStreetName(array $components)
    {
        $streetNameKeys = array('road', 'street', 'street_name', 'residential');

        return $this->guessBestComponent($components, $streetNameKeys);
    }

    /**
     * @param array $components
     *
     * @return null|string
     */
    protected function guessSubLocality(array $components)
    {
        $subLocalityKeys = array('neighbourhood', 'city_district');

        return $this->guessBestComponent($components, $subLocalityKeys);
    }

    /**
     * @param array $components
     * @param array $keys
     *
     * @return null|string
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
