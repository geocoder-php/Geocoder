<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GoogleMaps;

use Exception;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Exception\ZeroResults;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\AbstractHttpProvider;
use Geocoder\Provider\LocaleAwareGeocoder;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class GoogleMaps extends AbstractHttpProvider implements LocaleAwareGeocoder, Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL_SSL = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL_SSL = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=%F,%F';

    /**
     * @var string
     */
    private $region;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * Google Maps for Business
     * https://developers.google.com/maps/documentation/business/.
     *
     * @param HttpClient $client     An HTTP adapter
     * @param string     $clientId   Your Client ID
     * @param string     $privateKey Your Private Key (optional)
     * @param string     $region     Region biasing (optional)
     * @param string     $apiKey     Google Geocoding API key (optional)
     *
     * @return GoogleMaps
     */
    public static function business(HttpClient $client, $clientId, $privateKey = null, $region = null, $apiKey = null)
    {
        $provider = new self($client, $region, $apiKey);
        $provider->clientId = $clientId;
        $provider->privateKey = $privateKey;

        return $provider;
    }

    /**
     * @param HttpClient $client An HTTP adapter
     * @param string     $region Region biasing (optional)
     * @param string     $apiKey Google Geocoding API key (optional)
     */
    public function __construct(HttpClient $client, $region = null, $apiKey = null)
    {
        parent::__construct($client);

        $this->region = $region;
        $this->apiKey = $apiKey;
    }

    public function geocodeQuery(GeocodeQuery $query)
    {
        // Google API returns invalid data if IP address given
        // This API doesn't handle IPs
        if (filter_var($query->getText(), FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The GoogleMaps provider does not support IP addresses, only street addresses.');
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL_SSL, rawurlencode($query->getText()));

        return $this->fetchUrl($url, $query->getLocale(), $query->getLimit());
    }

    public function reverseQuery(ReverseQuery $query)
    {
        $coordinate = $query->getCoordinates();
        $url = sprintf(self::REVERSE_ENDPOINT_URL_SSL, $coordinate->getLatitude(), $coordinate->getLongitude());

        return $this->fetchUrl($url, $query->getLocale(), $query->getLimit());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'google_maps';
    }

    /**
     * @param $region
     *
     * @return GoogleMaps
     */
    public function withRegion($region)
    {
        $new = clone $this;
        $new->region = $region;

        return $new;
    }

    /**
     * @param string $url
     * @param string $locale
     *
     * @return string query with extra params
     */
    private function buildQuery($url, $locale)
    {
        if (null !== $locale) {
            $url = sprintf('%s&language=%s', $url, $locale);
        }

        if (null !== $this->region) {
            $url = sprintf('%s&region=%s', $url, $this->region);
        }

        if (null !== $this->apiKey) {
            $url = sprintf('%s&key=%s', $url, $this->apiKey);
        }

        if (null !== $this->clientId) {
            $url = sprintf('%s&client=%s', $url, $this->clientId);

            if (null !== $this->privateKey) {
                $url = $this->signQuery($url);
            }
        }

        return $url;
    }

    /**
     * @param string $url
     * @param string $locale
     * @param int    $limit
     *
     * @return \Geocoder\Collection
     *
     * @throws Exception
     */
    private function fetchUrl($url, $locale, $limit)
    {
        $url = $this->buildQuery($url, $locale);
        $request = $this->getMessageFactory()->createRequest('GET', $url);
        $content = (string) $this->getHttpClient()->sendRequest($request)->getBody();

        // Throw exception if invalid clientID and/or privateKey used with GoogleMapsBusinessProvider
        if (strpos($content, "Provided 'signature' is not valid for the provided client ID") !== false) {
            throw new InvalidCredentials(sprintf('Invalid client ID / API Key %s', $url));
        }

        if (empty($content)) {
            throw InvalidServerResponse::create($url);
        }

        $json = json_decode($content);

        // API error
        if (!isset($json)) {
            throw InvalidServerResponse::create($url);
        }

        if ('REQUEST_DENIED' === $json->status && 'The provided API key is invalid.' === $json->error_message) {
            throw new InvalidCredentials(sprintf('API key is invalid %s', $url));
        }

        if ('REQUEST_DENIED' === $json->status) {
            throw new Exception(sprintf('API access denied. Request: %s - Message: %s',
                $url, $json->error_message));
        }

        // you are over your quota
        if ('OVER_QUERY_LIMIT' === $json->status) {
            throw new QuotaExceeded(sprintf('Daily quota exceeded %s', $url));
        }

        // no result
        if (!isset($json->results) || !count($json->results) || 'OK' !== $json->status) {
            throw ZeroResults::create($url);
        }

        $results = [];
        foreach ($json->results as $result) {
            $resultSet = $this->getDefaults();

            // update address components
            foreach ($result->address_components as $component) {
                foreach ($component->types as $type) {
                    $this->updateAddressComponent($resultSet, $type, $component);
                }
            }

            // update coordinates
            $coordinates = $result->geometry->location;
            $resultSet['latitude'] = $coordinates->lat;
            $resultSet['longitude'] = $coordinates->lng;

            $resultSet['bounds'] = null;
            if (isset($result->geometry->bounds)) {
                $resultSet['bounds'] = [
                    'south' => $result->geometry->bounds->southwest->lat,
                    'west' => $result->geometry->bounds->southwest->lng,
                    'north' => $result->geometry->bounds->northeast->lat,
                    'east' => $result->geometry->bounds->northeast->lng,
                ];
            } elseif ('ROOFTOP' === $result->geometry->location_type) {
                // Fake bounds
                $resultSet['bounds'] = [
                    'south' => $coordinates->lat,
                    'west' => $coordinates->lng,
                    'north' => $coordinates->lat,
                    'east' => $coordinates->lng,
                ];
            }

            $results[] = array_merge($this->getDefaults(), $resultSet);

            if (count($results) >= $limit) {
                break;
            }
        }

        return $this->returnResults($results);
    }

    /**
     * Update current resultSet with given key/value.
     *
     * @param array  $resultSet resultSet to update
     * @param string $type      Component type
     * @param object $values    The component values
     *
     * @return array
     */
    private function updateAddressComponent(&$resultSet, $type, $values)
    {
        switch ($type) {
            case 'postal_code':
                $resultSet['postalCode'] = $values->long_name;
                break;

            case 'locality':
            case 'postal_town':
                $resultSet['locality'] = $values->long_name;
                break;

            case 'administrative_area_level_1':
            case 'administrative_area_level_2':
            case 'administrative_area_level_3':
            case 'administrative_area_level_4':
            case 'administrative_area_level_5':
                $resultSet['adminLevels'][] = [
                    'name' => $values->long_name,
                    'code' => $values->short_name,
                    'level' => intval(substr($type, -1)),
                ];
                break;

            case 'country':
                $resultSet['country'] = $values->long_name;
                $resultSet['countryCode'] = $values->short_name;
                break;

            case 'street_number':
                $resultSet['streetNumber'] = $values->long_name;
                break;

            case 'route':
                $resultSet['streetName'] = $values->long_name;
                break;

            case 'sublocality':
                $resultSet['subLocality'] = $values->long_name;
                break;

            default:
        }

        return $resultSet;
    }

    /**
     * Sign a URL with a given crypto key
     * Note that this URL must be properly URL-encoded
     * src: http://gmaps-samples.googlecode.com/svn/trunk/urlsigning/UrlSigner.php-source.
     *
     * @param string $query Query to be signed
     *
     * @return string $query query with signature appended
     */
    private function signQuery($query)
    {
        $url = parse_url($query);

        $urlPartToSign = $url['path'].'?'.$url['query'];

        // Decode the private key into its binary format
        $decodedKey = base64_decode(str_replace(['-', '_'], ['+', '/'], $this->privateKey));

        // Create a signature using the private key and the URL-encoded
        // string using HMAC SHA1. This signature will be binary.
        $signature = hash_hmac('sha1', $urlPartToSign, $decodedKey, true);

        $encodedSignature = str_replace(['+', '/'], ['-', '_'], base64_encode($signature));

        return sprintf('%s&signature=%s', $query, $encodedSignature);
    }
}
