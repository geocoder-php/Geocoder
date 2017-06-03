<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GoogleMaps;

use Exception;
use Geocoder\Collection;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\AddressBuilder;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\AbstractHttpProvider;
use Geocoder\Provider\GoogleMaps\Model\GoogleAddress;
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

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        // Google API returns invalid data if IP address given
        // This API doesn't handle IPs
        if (filter_var($query->getText(), FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The GoogleMaps provider does not support IP addresses, only street addresses.');
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL_SSL, rawurlencode($query->getText()));
        if (null !== $bounds = $query->getBounds()) {
            $url .= sprintf('&bounds=%s,%s|%s,%s', $bounds->getSouth(), $bounds->getWest(), $bounds->getNorth(), $bounds->getEast());
        }

        return $this->fetchUrl($url, $query->getLocale(), $query->getLimit(), $query->getData('region', $this->region));
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinate = $query->getCoordinates();
        $url = sprintf(self::REVERSE_ENDPOINT_URL_SSL, $coordinate->getLatitude(), $coordinate->getLongitude());

        if (null !== $locationType = $query->getData('location_type')) {
            $url .= '&location_type='.urlencode($locationType);
        }

        if (null !== $resultType = $query->getData('result_type')) {
            $url .= '&result_type='.urlencode($resultType);
        }

        return $this->fetchUrl($url, $query->getLocale(), $query->getLimit(), $query->getData('region', $this->region));
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'google_maps';
    }

    /**
     * @param string $url
     * @param string $locale
     *
     * @return string query with extra params
     */
    private function buildQuery($url, $locale, $region)
    {
        if (null !== $locale) {
            $url = sprintf('%s&language=%s', $url, $locale);
        }

        if (null !== $region) {
            $url = sprintf('%s&region=%s', $url, $region);
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
     * @param string $region
     *
     * @return \Geocoder\Collection
     *
     * @throws Exception
     */
    private function fetchUrl($url, $locale, $limit, $region)
    {
        $url = $this->buildQuery($url, $locale, $region);
        $content = $this->getUrlContents($url);

        // Throw exception if invalid clientID and/or privateKey used with GoogleMapsBusinessProvider
        if (strpos($content, "Provided 'signature' is not valid for the provided client ID") !== false) {
            throw new InvalidCredentials(sprintf('Invalid client ID / API Key %s', $url));
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
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($json->results as $result) {
            $builder = new AddressBuilder($this->getName());

            // update address components
            foreach ($result->address_components as $component) {
                foreach ($component->types as $type) {
                    $this->updateAddressComponent($builder, $type, $component);
                }
            }

            // update coordinates
            $coordinates = $result->geometry->location;
            $builder->setCoordinates($coordinates->lat, $coordinates->lng);

            if (isset($result->geometry->bounds)) {
                $builder->setBounds(
                    $result->geometry->bounds->southwest->lat,
                    $result->geometry->bounds->southwest->lng,
                    $result->geometry->bounds->northeast->lat,
                    $result->geometry->bounds->northeast->lng
                );
            } elseif ('ROOFTOP' === $result->geometry->location_type) {
                // Fake bounds
                $builder->setBounds(
                    $coordinates->lat,
                    $coordinates->lng,
                    $coordinates->lat,
                    $coordinates->lng
                );
            }

            /** @var GoogleAddress $address */
            $address = $builder->build(GoogleAddress::class);
            if (isset($result->geometry->location_type)) {
                $address = $address->withLocationType($result->geometry->location_type);
            }
            if (isset($result->types)) {
                $address = $address->withResultType($result->types);
            }
            if (isset($result->formatted_address)) {
                $address = $address->withFormattedAddress($result->formatted_address);
            }
            if ($builder->hasValue('subpremise')) {
                $address = $address->withSubpremise($builder->getValue('subpremise'));
            }
            $results[] = $address;

            if (count($results) >= $limit) {
                break;
            }
        }

        return new AddressCollection($results);
    }

    /**
     * Update current resultSet with given key/value.
     *
     * @param AddressBuilder $builder
     * @param string         $type    Component type
     * @param object         $values  The component values
     */
    private function updateAddressComponent(AddressBuilder $builder, $type, $values)
    {
        switch ($type) {
            case 'postal_code':
                $builder->setPostalCode($values->long_name);
                break;

            case 'locality':
            case 'postal_town':
                $builder->setLocality($values->long_name);
                break;

            case 'administrative_area_level_1':
            case 'administrative_area_level_2':
            case 'administrative_area_level_3':
            case 'administrative_area_level_4':
            case 'administrative_area_level_5':
                $builder->addAdminLevel(intval(substr($type, -1)), $values->long_name, $values->short_name);
                break;

            case 'country':
                $builder->setCountry($values->long_name);
                $builder->setCountryCode($values->short_name);
                break;

            case 'street_number':
                $builder->setStreetNumber($values->long_name);
                break;

            case 'route':
                $builder->setStreetName($values->long_name);
                break;

            case 'sublocality':
                $builder->setSubLocality($values->long_name);
                break;

            case 'subpremise':
                $builder->setValue('subpremise', $values->long_name);
                break;

            default:
        }
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
