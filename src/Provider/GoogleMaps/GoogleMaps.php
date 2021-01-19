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

use Geocoder\Collection;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\GoogleMaps\Model\GoogleAddress;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class GoogleMaps extends AbstractHttpProvider implements Provider
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
     * @var string|null
     */
    private $region;

    /**
     * @var string|null
     */
    private $apiKey;

    /**
     * @var string|null
     */
    private $clientId;

    /**
     * @var string|null
     */
    private $privateKey;

    /**
     * @var string|null
     */
    private $channel;

    /**
     * Google Maps for Business
     * https://developers.google.com/maps/documentation/business/
     * Maps for Business is no longer accepting new signups.
     *
     * @param ClientInterface $client     An HTTP adapter
     * @param string          $clientId   Your Client ID
     * @param string          $privateKey Your Private Key (optional)
     * @param string          $region     Region biasing (optional)
     * @param string          $apiKey     Google Geocoding API key (optional)
     * @param string          $channel    Google Channel parameter (optional)
     *
     * @return GoogleMaps
     */
    public static function business(
        ClientInterface $client,
        string $clientId,
        string $privateKey = null,
        string $region = null,
        string $apiKey = null,
        string $channel = null
    ) {
        $provider = new self($client, $region, $apiKey);
        $provider->clientId = $clientId;
        $provider->privateKey = $privateKey;
        $provider->channel = $channel;

        return $provider;
    }

    /**
     * @param ClientInterface $client An HTTP adapter
     * @param string          $region Region biasing (optional)
     * @param string          $apiKey Google Geocoding API key (optional)
     */
    public function __construct(ClientInterface $client, string $region = null, string $apiKey = null)
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
            $url .= sprintf(
                '&bounds=%s,%s|%s,%s',
                $bounds->getSouth(),
                $bounds->getWest(),
                $bounds->getNorth(),
                $bounds->getEast()
            );
        }

        if (null !== $components = $query->getData('components')) {
            $serializedComponents = is_string($components) ? $components : $this->serializeComponents($components);
            $url .= sprintf('&components=%s', urlencode($serializedComponents));
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
    private function buildQuery(string $url, string $locale = null, string $region = null): string
    {
        if (null === $this->apiKey && null === $this->clientId) {
            throw new InvalidCredentials('You must provide an API key. Keyless access was removed in June, 2016');
        }

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

            if (null !== $this->channel) {
                $url = sprintf('%s&channel=%s', $url, $this->channel);
            }

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
     * @return AddressCollection
     *
     * @throws InvalidServerResponse
     * @throws InvalidCredentials
     */
    private function fetchUrl(string $url, string $locale = null, int $limit, string $region = null): AddressCollection
    {
        $url = $this->buildQuery($url, $locale, $region);
        $content = $this->getUrlContents($url);
        $json = $this->validateResponse($url, $content);

        // no result
        if (!isset($json->results) || !count($json->results) || 'OK' !== $json->status) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($json->results as $result) {
            $builder = new AddressBuilder($this->getName());
            $this->parseCoordinates($builder, $result);

            // set official Google place id
            if (isset($result->place_id)) {
                $builder->setValue('id', $result->place_id);
            }

            // update address components
            foreach ($result->address_components as $component) {
                foreach ($component->types as $type) {
                    $this->updateAddressComponent($builder, $type, $component);
                }
            }

            /** @var GoogleAddress $address */
            $address = $builder->build(GoogleAddress::class);
            $address = $address->withId($builder->getValue('id'));
            if (isset($result->geometry->location_type)) {
                $address = $address->withLocationType($result->geometry->location_type);
            }
            if (isset($result->types)) {
                $address = $address->withResultType($result->types);
            }
            if (isset($result->formatted_address)) {
                $address = $address->withFormattedAddress($result->formatted_address);
            }

            $results[] = $address
                ->withStreetAddress($builder->getValue('street_address'))
                ->withIntersection($builder->getValue('intersection'))
                ->withPolitical($builder->getValue('political'))
                ->withColloquialArea($builder->getValue('colloquial_area'))
                ->withWard($builder->getValue('ward'))
                ->withNeighborhood($builder->getValue('neighborhood'))
                ->withPremise($builder->getValue('premise'))
                ->withSubpremise($builder->getValue('subpremise'))
                ->withNaturalFeature($builder->getValue('natural_feature'))
                ->withAirport($builder->getValue('airport'))
                ->withPark($builder->getValue('park'))
                ->withPointOfInterest($builder->getValue('point_of_interest'))
                ->withEstablishment($builder->getValue('establishment'))
                ->withSubLocalityLevels($builder->getValue('subLocalityLevel', []))
                ->withPostalCodeSuffix($builder->getValue('postal_code_suffix'))
                ->withPartialMatch($result->partial_match ?? false);

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
    private function updateAddressComponent(AddressBuilder $builder, string $type, $values)
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

            case 'sublocality_level_1':
            case 'sublocality_level_2':
            case 'sublocality_level_3':
            case 'sublocality_level_4':
            case 'sublocality_level_5':
                $subLocalityLevel = $builder->getValue('subLocalityLevel', []);
                $subLocalityLevel[] = [
                    'level' => intval(substr($type, -1)),
                    'name' => $values->long_name,
                    'code' => $values->short_name,
                ];
                $builder->setValue('subLocalityLevel', $subLocalityLevel);

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

            case 'street_address':
            case 'intersection':
            case 'political':
            case 'colloquial_area':
            case 'ward':
            case 'neighborhood':
            case 'premise':
            case 'subpremise':
            case 'natural_feature':
            case 'airport':
            case 'park':
            case 'point_of_interest':
            case 'establishment':
            case 'postal_code_suffix':
                $builder->setValue($type, $values->long_name);

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
    private function signQuery(string $query): string
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

    /**
     * Serialize the component query parameter.
     *
     * @param array $components
     *
     * @return string
     */
    private function serializeComponents(array $components): string
    {
        return implode('|', array_map(function ($name, $value) {
            return sprintf('%s:%s', $name, $value);
        }, array_keys($components), $components));
    }

    /**
     * Decode the response content and validate it to make sure it does not have any errors.
     *
     * @param string $url
     * @param string $content
     *
     * @return \Stdclass result form json_decode()
     *
     * @throws InvalidCredentials
     * @throws InvalidServerResponse
     * @throws QuotaExceeded
     */
    private function validateResponse(string $url, $content)
    {
        // Throw exception if invalid clientID and/or privateKey used with GoogleMapsBusinessProvider
        if (false !== strpos($content, "Provided 'signature' is not valid for the provided client ID")) {
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
            throw new InvalidServerResponse(sprintf('API access denied. Request: %s - Message: %s', $url, $json->error_message));
        }

        // you are over your quota
        if ('OVER_QUERY_LIMIT' === $json->status) {
            throw new QuotaExceeded(sprintf('Daily quota exceeded %s', $url));
        }

        return $json;
    }

    /**
     * Parse coordinates and bounds.
     *
     * @param AddressBuilder $builder
     * @param \Stdclass      $result
     */
    private function parseCoordinates(AddressBuilder $builder, $result)
    {
        $coordinates = $result->geometry->location;
        $builder->setCoordinates($coordinates->lat, $coordinates->lng);

        if (isset($result->geometry->bounds)) {
            $builder->setBounds(
                $result->geometry->bounds->southwest->lat,
                $result->geometry->bounds->southwest->lng,
                $result->geometry->bounds->northeast->lat,
                $result->geometry->bounds->northeast->lng
            );
        } elseif (isset($result->geometry->viewport)) {
            $builder->setBounds(
                $result->geometry->viewport->southwest->lat,
                $result->geometry->viewport->southwest->lng,
                $result->geometry->viewport->northeast->lat,
                $result->geometry->viewport->northeast->lng
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
    }
}
