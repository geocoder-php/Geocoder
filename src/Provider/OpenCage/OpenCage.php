<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\OpenCage;

use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Collection;
use Geocoder\Exception\ZeroResults;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\AbstractHttpProvider;
use Geocoder\Provider\LocaleAwareGeocoder;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author mtm <mtm@opencagedata.com>
 */
final class OpenCage extends AbstractHttpProvider implements LocaleAwareGeocoder, Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'https://api.opencagedata.com/geocode/v1/json?key=%s&query=%s&limit=%d&pretty=1';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param HttpClient $client an HTTP adapter
     * @param string     $apiKey an API key
     */
    public function __construct(HttpClient $client, $apiKey)
    {
        parent::__construct($client);

        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query)
    {
        $address = $query->getText();
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided.');
        }

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The OpenCage provider does not support IP addresses, only street addresses.');
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL, $this->apiKey, urlencode($address), $query->getLimit());

        return $this->executeQuery($url, $query->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query)
    {
        $coordinates = $query->getCoordinates();
        $address = sprintf('%f, %f', $coordinates->getLatitude(), $coordinates->getLongitude());

        return $this->geocodeQuery(GeocodeQuery::create($address)->withLocale($query->getLocale()));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'opencage';
    }

    /**
     * @param $query
     *
     * @return Collection
     */
    private function executeQuery($query, $locale)
    {
        if (null !== $locale) {
            $query = sprintf('%s&language=%s', $query, $locale);
        }

        $request = $this->getMessageFactory()->createRequest('GET', $query);
        $content = (string) $this->getHttpClient()->sendRequest($request)->getBody();

        if (empty($content)) {
            throw InvalidServerResponse::create($query);
        }

        $json = json_decode($content, true);

        // https://geocoder.opencagedata.com/api#codes
        if (isset($json['status'])) {
            switch ($json['status']['code']) {
                case 400:
                    throw new InvalidArgument('Invalid request (a required parameter is missing).');
                case 402:
                    throw new QuotaExceeded('Valid request but quota exceeded.');
                case 403:
                    throw new InvalidCredentials('Invalid or missing api key.');
            }
        }

        if (!isset($json['total_results']) || $json['total_results'] == 0) {
            throw ZeroResults::create($query);
        }

        $locations = $json['results'];

        if (empty($locations)) {
            throw ZeroResults::create($query);
        }

        $results = [];
        foreach ($locations as $location) {
            $bounds = [];
            if (isset($location['bounds'])) {
                $bounds = [
                    'south' => $location['bounds']['southwest']['lat'],
                    'west' => $location['bounds']['southwest']['lng'],
                    'north' => $location['bounds']['northeast']['lat'],
                    'east' => $location['bounds']['northeast']['lng'],
                ];
            }

            $comp = $location['components'];

            $adminLevels = [];
            foreach (['state', 'county'] as $i => $component) {
                if (isset($comp[$component])) {
                    $adminLevels[] = ['name' => $comp[$component], 'level' => $i + 1];
                }
            }

            $results[] = array_merge($this->getDefaults(), [
                'latitude' => $location['geometry']['lat'],
                'longitude' => $location['geometry']['lng'],
                'bounds' => $bounds ?: [],
                'streetNumber' => isset($comp['house_number']) ? $comp['house_number'] : null,
                'streetName' => $this->guessStreetName($comp),
                'subLocality' => $this->guessSubLocality($comp),
                'locality' => $this->guessLocality($comp),
                'postalCode' => isset($comp['postcode']) ? $comp['postcode'] : null,
                'adminLevels' => $adminLevels,
                'country' => isset($comp['country']) ? $comp['country'] : null,
                'countryCode' => isset($comp['country_code']) ? strtoupper($comp['country_code']) : null,
                'timezone' => isset($location['annotations']['timezone']['name']) ? $location['annotations']['timezone']['name'] : null,
            ]);
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
        $localityKeys = ['city', 'town', 'village', 'hamlet'];

        return $this->guessBestComponent($components, $localityKeys);
    }

    /**
     * @param array $components
     *
     * @return null|string
     */
    protected function guessStreetName(array $components)
    {
        $streetNameKeys = ['road', 'street', 'street_name', 'residential'];

        return $this->guessBestComponent($components, $streetNameKeys);
    }

    /**
     * @param array $components
     *
     * @return null|string
     */
    protected function guessSubLocality(array $components)
    {
        $subLocalityKeys = ['suburb', 'neighbourhood', 'city_district'];

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
