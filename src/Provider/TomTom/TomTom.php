<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\TomTom;

use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\AbstractHttpProvider;
use Geocoder\Provider\LocaleAwareGeocoder;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
final class TomTom extends AbstractHttpProvider implements LocaleAwareGeocoder, Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'https://api.tomtom.com/lbs/services/geocode/4/geocode?key=%s&query=%s&maxResults=%d';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'https://api.tomtom.com/lbs/services/reverseGeocode/3/xml?key=%s&point=%F,%F';

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
            throw new UnsupportedOperation('The TomTom provider does not support IP addresses, only street addresses.');
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL, $this->apiKey, rawurlencode($address), $query->getLimit());

        return $this->executeQuery($url, $query->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query)
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No Map API Key provided.');
        }

        $url = sprintf(self::REVERSE_ENDPOINT_URL, $this->apiKey, $latitude, $longitude);

        return $this->executeQuery($url, $query->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tomtom';
    }

    /**
     * @param string $url
     */
    private function executeQuery($url, $locale)
    {
        if (null !== $locale) {
            // Supported 2- character values are de, en, es, fr, it, nl, pl, pt, and sv.
            // Equivalent 3-character values are GER, ENG, SPA, FRE, ITA, DUT, POL, POR, and SWE.
            $url = sprintf('%s&language=%s', $url, substr($locale, 0, 2));
        }

        $content = $this->getUrlContents($url);
        if (false !== stripos($content, 'Developer Inactive')) {
            throw new InvalidCredentials('Map API Key provided is not valid.');
        }

        try {
            $xml = new \SimpleXmlElement($content);
        } catch (\Exception $e) {
            throw InvalidServerResponse::create($url);
        }

        $attributes = $xml->attributes();

        if (isset($attributes['count']) && 0 === (int) $attributes['count']) {
            return new AddressCollection([]);
        }

        if (isset($attributes['errorCode'])) {
            if ('403' === (string) $attributes['errorCode']) {
                throw new InvalidCredentials('Map API Key provided is not valid.');
            }

            return new AddressCollection([]);
        }

        $data = isset($xml->geoResult) ? $xml->geoResult : $xml->reverseGeoResult;

        if (0 === count($data)) {
            return $this->returnResults([$this->getResultArray($data)]);
        }

        $results = [];
        foreach ($data as $item) {
            $results[] = $this->getResultArray($item);
        }

        return $this->returnResults($results);
    }

    private function getResultArray(\SimpleXmlElement $data)
    {
        return array_merge($this->getDefaults(), [
            'latitude' => isset($data->latitude) ? (float) $data->latitude : null,
            'longitude' => isset($data->longitude) ? (float) $data->longitude : null,
            'streetName' => isset($data->street) ? (string) $data->street : null,
            'locality' => isset($data->city) ? (string) $data->city : null,
            'adminLevels' => isset($data->state) ? [['name' => (string) $data->state, 'level' => 1]] : [],
            'country' => isset($data->country) ? (string) $data->country : null,
            'countryCode' => isset($data->countryISO3) ? (string) $data->countryISO3 : null,
        ]);
    }
}
