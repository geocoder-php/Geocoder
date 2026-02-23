<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Here;

use Geocoder\Collection;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Here\Model\HereAddress;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\Query;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;

/**
 * @author Sébastien Barré <sebastien@sheub.eu>
 */
final class Here extends AbstractHttpProvider implements Provider
{
    /**
     * HERE Geocoding & Search API (current, recommended).
     */
    public const API_V8 = 'v8';

    /**
     * Legacy HERE Geocoder REST API (retired December 31, 2023).
     *
     * @deprecated The legacy HERE Geocoder REST API was retired on December 31, 2023.
     *             Use {@see API_V8} and {@see createUsingApiKey()} instead.
     */
    public const API_V7 = 'v7';

    /**
     * HERE Geocoding & Search API geocode endpoint (v8).
     *
     * @var string
     */
    public const GEOCODE_ENDPOINT_URL = 'https://geocode.search.hereapi.com/v1/geocode';

    /**
     * HERE Geocoding & Search API reverse geocode endpoint (v8).
     *
     * @var string
     */
    public const REVERSE_ENDPOINT_URL = 'https://revgeocode.search.hereapi.com/v1/revgeocode';

    /**
     * @deprecated Use {@see GEOCODE_ENDPOINT_URL} with the v8 API instead.
     *
     * @var string
     */
    public const GEOCODE_ENDPOINT_URL_API_KEY = 'https://geocoder.ls.hereapi.com/6.2/geocode.json';

    /**
     * @deprecated Use {@see GEOCODE_ENDPOINT_URL} with the v8 API instead.
     *
     * @var string
     */
    public const GEOCODE_ENDPOINT_URL_APP_CODE = 'https://geocoder.api.here.com/6.2/geocode.json';

    /**
     * @deprecated Use {@see GEOCODE_ENDPOINT_URL} with the v8 API instead.
     *
     * @var string
     */
    public const GEOCODE_CIT_ENDPOINT_API_KEY = 'https:/geocoder.sit.ls.hereapi.com/6.2/geocode.json';

    /**
     * @deprecated Use {@see GEOCODE_ENDPOINT_URL} with the v8 API instead.
     *
     * @var string
     */
    public const GEOCODE_CIT_ENDPOINT_APP_CODE = 'https://geocoder.cit.api.here.com/6.2/geocode.json';

    /**
     * @deprecated Use {@see REVERSE_ENDPOINT_URL} with the v8 API instead.
     *
     * @var string
     */
    public const REVERSE_ENDPOINT_URL_API_KEY = 'https://reverse.geocoder.ls.hereapi.com/6.2/reversegeocode.json';

    /**
     * @deprecated Use {@see REVERSE_ENDPOINT_URL} with the v8 API instead.
     *
     * @var string
     */
    public const REVERSE_ENDPOINT_URL_APP_CODE = 'https://reverse.geocoder.api.here.com/6.2/reversegeocode.json';

    /**
     * @deprecated Use {@see REVERSE_ENDPOINT_URL} with the v8 API instead.
     *
     * @var string
     */
    public const REVERSE_CIT_ENDPOINT_URL_API_KEY = 'https://reverse.geocoder.sit.ls.hereapi.com/6.2/reversegeocode.json';

    /**
     * @deprecated Use {@see REVERSE_ENDPOINT_URL} with the v8 API instead.
     *
     * @var string
     */
    public const REVERSE_CIT_ENDPOINT_URL_APP_CODE = 'https://reverse.geocoder.cit.api.here.com/6.2/reversegeocode.json';

    /**
     * Additional data parameters supported by the v7 (legacy) geocode endpoint.
     *
     * @deprecated These parameters are only available in the legacy HERE Geocoder API (v7),
     *             which was retired on December 31, 2023. Migrate to the v8 API.
     *
     * @var string[]
     */
    public const GEOCODE_ADDITIONAL_DATA_PARAMS = [
        'CrossingStreets',
        'PreserveUnitDesignators',
        'Country2',
        'IncludeChildPOIs',
        'IncludeRoutingInformation',
        'AdditionalAddressProvider',
        'HouseNumberMode',
        'FlexibleAdminValues',
        'IntersectionSnapTolerance',
        'AddressRangeSqueezeOffset',
        'AddressRangeSqueezeFactor',
        'AddressRangeSqueezeOffset',
        'IncludeShapeLevel',
        'RestrictLevel',
        'SuppressStreetType',
        'NormalizeNames',
        'IncludeMicroPointAddresses',
    ];

    /**
     * @var string
     */
    private $appId;

    /**
     * @var string
     */
    private $appCode;

    /**
     * @var bool
     */
    private $useCIT;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiVersion;

    /**
     * Creates a v7 (legacy) provider instance. For new integrations use {@see createUsingApiKey()}.
     *
     * @deprecated The legacy HERE Geocoder REST API was retired on December 31, 2023.
     *             Use {@see createUsingApiKey()} with the v8 Geocoding & Search API instead.
     *
     * @param ClientInterface $client     an HTTP adapter
     * @param string          $appId      a HERE App ID (v7 only)
     * @param string          $appCode    a HERE App Code (v7 only)
     * @param bool            $useCIT     use Customer Integration Testing environment (v7 only)
     */
    public function __construct(ClientInterface $client, ?string $appId = null, ?string $appCode = null, bool $useCIT = false, string $apiVersion = self::API_V7)
    {
        $this->appId = $appId;
        $this->appCode = $appCode;
        $this->useCIT = $useCIT;
        $this->apiVersion = $apiVersion;

        parent::__construct($client);
    }

    /**
     * Create a v8 (HERE Geocoding & Search API) provider using an API Key.
     *
     * This is the recommended factory method for all new integrations.
     * See https://www.here.com/docs/bundle/geocoding-and-search-api-migration-guide/page/migration-geocoder/README.html
     * for migration instructions from the legacy v7 API.
     */
    public static function createUsingApiKey(ClientInterface $client, string $apiKey): self
    {
        $instance = new self($client, null, null, false, self::API_V8);
        $instance->apiKey = $apiKey;

        return $instance;
    }

    /**
     * Create a v7 (legacy HERE Geocoder API) provider using an API Key.
     *
     * @deprecated The legacy HERE Geocoder REST API was retired on December 31, 2023.
     *             Migrate to {@see createUsingApiKey()} with the v8 Geocoding & Search API.
     *             See https://www.here.com/docs/bundle/geocoding-and-search-api-migration-guide/page/migration-geocoder/README.html
     */
    public static function createV7UsingApiKey(ClientInterface $client, string $apiKey, bool $useCIT = false): self
    {
        $instance = new self($client, null, null, $useCIT, self::API_V7);
        $instance->apiKey = $apiKey;

        return $instance;
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        // This API doesn't handle IPs
        if (filter_var($query->getText(), FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Here provider does not support IP addresses, only street addresses.');
        }

        if (self::API_V8 === $this->apiVersion) {
            return $this->geocodeQueryV8($query);
        }

        return $this->geocodeQueryV7($query);
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        if (self::API_V8 === $this->apiVersion) {
            return $this->reverseQueryV8($query);
        }

        return $this->reverseQueryV7($query);
    }

    private function geocodeQueryV8(GeocodeQuery $query): Collection
    {
        $queryParams = [
            'q' => $query->getText(),
            'limit' => $query->getLimit(),
            'apiKey' => $this->apiKey,
        ];

        // Pass-through for Geocoding & Search API geo filters / sorting reference point.
        // See https://www.here.com/docs/bundle/geocoding-and-search-api-v7-api-reference/page/index.html#/paths/~1geocode/get
        if (null !== $at = $query->getData('at')) {
            $queryParams['at'] = $at;
        }
        if (null !== $in = $query->getData('in')) {
            $queryParams['in'] = $in;
        }
        if (null !== $types = $query->getData('types')) {
            $queryParams['types'] = $types;
        }

        $qq = [];
        if (null !== $country = $query->getData('country')) {
            $qq[] = 'country=' . $country;
        }
        if (null !== $state = $query->getData('state')) {
            $qq[] = 'state=' . $state;
        }
        if (null !== $county = $query->getData('county')) {
            $qq[] = 'county=' . $county;
        }
        if (null !== $city = $query->getData('city')) {
            $qq[] = 'city=' . $city;
        }

        if (!empty($qq)) {
            $queryParams['qq'] = implode(';', $qq);
        }

        if (null !== $query->getLocale()) {
            $queryParams['lang'] = $query->getLocale();
        }

        return $this->executeQuery(sprintf('%s?%s', self::GEOCODE_ENDPOINT_URL, http_build_query($queryParams)), $query->getLimit());
    }

    private function geocodeQueryV7(GeocodeQuery $query): Collection
    {
        $queryParams = $this->withApiCredentials([
            'searchtext' => $query->getText(),
            'gen' => 9,
            'additionaldata' => $this->getAdditionalDataParam($query),
        ]);

        if (null !== $query->getData('country')) {
            $queryParams['country'] = $query->getData('country');
        }

        if (null !== $query->getData('state')) {
            $queryParams['state'] = $query->getData('state');
        }

        if (null !== $query->getData('county')) {
            $queryParams['county'] = $query->getData('county');
        }

        if (null !== $query->getData('city')) {
            $queryParams['city'] = $query->getData('city');
        }

        if (null !== $query->getLocale()) {
            $queryParams['language'] = $query->getLocale();
        }

        return $this->executeQuery(sprintf('%s?%s', $this->getBaseUrl($query), http_build_query($queryParams)), $query->getLimit());
    }

    private function reverseQueryV8(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();

        $queryParams = [
            'at' => sprintf('%s,%s', $coordinates->getLatitude(), $coordinates->getLongitude()),
            'limit' => $query->getLimit(),
            'apiKey' => $this->apiKey,
        ];

        if (null !== $query->getLocale()) {
            $queryParams['lang'] = $query->getLocale();
        }

        return $this->executeQuery(sprintf('%s?%s', self::REVERSE_ENDPOINT_URL, http_build_query($queryParams)), $query->getLimit());
    }

    private function reverseQueryV7(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();

        $queryParams = $this->withApiCredentials([
            'gen' => 9,
            'mode' => 'retrieveAddresses',
            'prox' => sprintf('%s,%s', $coordinates->getLatitude(), $coordinates->getLongitude()),
            'maxresults' => $query->getLimit(),
        ]);

        return $this->executeQuery(sprintf('%s?%s', $this->getBaseUrl($query), http_build_query($queryParams)), $query->getLimit());
    }

    private function executeQuery(string $url, int $limit): Collection
    {
        $content = $this->getUrlContents($url);

        $json = json_decode($content, true);

        if (self::API_V8 === $this->apiVersion) {
            // v8 error format: {"error":"Unauthorized","error_description":"..."}
            if (isset($json['error']) && 'Unauthorized' === $json['error']) {
                throw new InvalidCredentials('Invalid or missing api key.');
            }

            if (isset($json['items'])) {
                return $this->parseV8Response($json['items'], $limit);
            }

            return new AddressCollection([]);
        }

        // v7 error format: {"type":{"subtype":"InvalidCredentials"}}
        if (isset($json['type'])) {
            switch ($json['type']['subtype']) {
                case 'InvalidInputData':
                    throw new InvalidArgument('Input parameter validation failed.');
                case 'QuotaExceeded':
                    throw new QuotaExceeded('Valid request but quota exceeded.');
                case 'InvalidCredentials':
                    throw new InvalidCredentials('Invalid or missing api key.');
            }
        }

        if (!isset($json['Response']) || empty($json['Response'])) {
            return new AddressCollection([]);
        }

        if (!isset($json['Response']['View'][0])) {
            return new AddressCollection([]);
        }

        return $this->parseV7Response($json['Response']['View'][0]['Result'], $limit);
    }

    private function parseV8Response(array $items, int $limit): Collection
    {
        $results = [];

        foreach ($items as $item) {
            $builder = new AddressBuilder($this->getName());

            $position = $item['position'];
            $builder->setCoordinates($position['lat'], $position['lng']);

            if (isset($item['mapView'])) {
                $mapView = $item['mapView'];
                $builder->setBounds($mapView['south'], $mapView['west'], $mapView['north'], $mapView['east']);
            }

            $address = $item['address'];
            $builder->setStreetNumber($address['houseNumber'] ?? null);
            $builder->setStreetName($address['street'] ?? null);
            $builder->setPostalCode($address['postalCode'] ?? null);
            $builder->setLocality($address['city'] ?? null);
            // The Geocoding & Search API may provide both `district` and `subdistrict`. Prefer `district`
            // for backward compatibility, but fall back to `subdistrict` when `district` is missing.
            $builder->setSubLocality($address['district'] ?? ($address['subdistrict'] ?? null));
            $builder->setCountryCode($address['countryCode'] ?? null);
            $builder->setCountry($address['countryName'] ?? null);

            /** @var HereAddress $hereAddress */
            $hereAddress = $builder->build(HereAddress::class);
            $hereAddress = $hereAddress->withLocationId($item['id'] ?? null);
            $hereAddress = $hereAddress->withLocationType($item['resultType'] ?? null);
            $hereAddress = $hereAddress->withLocationName($item['title'] ?? null);

            $additionalData = [];
            if (isset($address['label'])) {
                $additionalData[] = ['key' => 'Label', 'value' => $address['label']];
            }
            if (isset($address['countryName'])) {
                $additionalData[] = ['key' => 'CountryName', 'value' => $address['countryName']];
            }
            if (isset($address['state'])) {
                $additionalData[] = ['key' => 'StateName', 'value' => $address['state']];
            }
            if (isset($address['stateCode'])) {
                $additionalData[] = ['key' => 'StateCode', 'value' => $address['stateCode']];
            }
            if (isset($address['county'])) {
                $additionalData[] = ['key' => 'CountyName', 'value' => $address['county']];
            }
            if (isset($address['countyCode'])) {
                $additionalData[] = ['key' => 'CountyCode', 'value' => $address['countyCode']];
            }
            if (isset($address['district'])) {
                $additionalData[] = ['key' => 'District', 'value' => $address['district']];
            }
            if (isset($address['subdistrict'])) {
                $additionalData[] = ['key' => 'Subdistrict', 'value' => $address['subdistrict']];
            }
            if (isset($address['streets'])) {
                $additionalData[] = ['key' => 'Streets', 'value' => $address['streets']];
            }
            if (isset($address['block'])) {
                $additionalData[] = ['key' => 'Block', 'value' => $address['block']];
            }
            if (isset($address['subblock'])) {
                $additionalData[] = ['key' => 'Subblock', 'value' => $address['subblock']];
            }
            if (isset($address['building'])) {
                $additionalData[] = ['key' => 'Building', 'value' => $address['building']];
            }
            if (isset($address['unit'])) {
                $additionalData[] = ['key' => 'Unit', 'value' => $address['unit']];
            }

            // Item-level metadata
            foreach (
                [
                    'politicalView' => 'PoliticalView',
                    'houseNumberType' => 'HouseNumberType',
                    'addressBlockType' => 'AddressBlockType',
                    'localityType' => 'LocalityType',
                    'administrativeAreaType' => 'AdministrativeAreaType',
                    'distance' => 'Distance',
                ] as $sourceKey => $targetKey
            ) {
                if (isset($item[$sourceKey])) {
                    $additionalData[] = ['key' => $targetKey, 'value' => $item[$sourceKey]];
                }
            }

            $hereAddress = $hereAddress->withAdditionalData($additionalData);
            $results[] = $hereAddress;

            if (count($results) >= $limit) {
                break;
            }
        }

        return new AddressCollection($results);
    }

    private function parseV7Response(array $locations, int $limit): Collection
    {
        $results = [];

        foreach ($locations as $loc) {
            $location = $loc['Location'];
            $builder = new AddressBuilder($this->getName());
            $coordinates = isset($location['NavigationPosition'][0]) ? $location['NavigationPosition'][0] : $location['DisplayPosition'];
            $builder->setCoordinates($coordinates['Latitude'], $coordinates['Longitude']);
            $bounds = $location['MapView'];

            $builder->setBounds($bounds['BottomRight']['Latitude'], $bounds['TopLeft']['Longitude'], $bounds['TopLeft']['Latitude'], $bounds['BottomRight']['Longitude']);
            $builder->setStreetNumber($location['Address']['HouseNumber'] ?? null);
            $builder->setStreetName($location['Address']['Street'] ?? null);
            $builder->setPostalCode($location['Address']['PostalCode'] ?? null);
            $builder->setLocality($location['Address']['City'] ?? null);
            $builder->setSubLocality($location['Address']['District'] ?? null);
            $builder->setCountryCode($location['Address']['Country'] ?? null);

            // The name of the country can be found in the AdditionalData.
            $additionalData = $location['Address']['AdditionalData'] ?? null;
            if (!empty($additionalData)) {
                $builder->setCountry($additionalData[array_search('CountryName', array_column($additionalData, 'key'))]['value'] ?? null);
            }

            // There may be a second AdditionalData. For example if "IncludeRoutingInformation" parameter is added
            $extraAdditionalData = $loc['AdditionalData'] ?? [];

            /** @var HereAddress $address */
            $address = $builder->build(HereAddress::class);
            $address = $address->withLocationId($location['LocationId'] ?? null);
            $address = $address->withLocationType($location['LocationType']);
            $address = $address->withAdditionalData(array_merge($additionalData ?? [], $extraAdditionalData));
            $address = $address->withShape($location['Shape'] ?? null);
            $results[] = $address;

            if (count($results) >= $limit) {
                break;
            }
        }

        return new AddressCollection($results);
    }

    public function getName(): string
    {
        return 'Here';
    }

    public function getBaseUrl(Query $query): string
    {
        if (self::API_V8 === $this->apiVersion) {
            return ($query instanceof ReverseQuery) ? self::REVERSE_ENDPOINT_URL : self::GEOCODE_ENDPOINT_URL;
        }

        $usingApiKey = null !== $this->apiKey;

        if ($query instanceof ReverseQuery) {
            if ($this->useCIT) {
                return $usingApiKey ? self::REVERSE_CIT_ENDPOINT_URL_API_KEY : self::REVERSE_CIT_ENDPOINT_URL_APP_CODE;
            }

            return $usingApiKey ? self::REVERSE_ENDPOINT_URL_API_KEY : self::REVERSE_ENDPOINT_URL_APP_CODE;
        }

        if ($this->useCIT) {
            return $usingApiKey ? self::GEOCODE_CIT_ENDPOINT_API_KEY : self::GEOCODE_CIT_ENDPOINT_APP_CODE;
        }

        return $usingApiKey ? self::GEOCODE_ENDPOINT_URL_API_KEY : self::GEOCODE_ENDPOINT_URL_APP_CODE;
    }

    /**
     * Get serialized additional data param (v7 only).
     */
    private function getAdditionalDataParam(GeocodeQuery $query): string
    {
        $additionalDataParams = [
            'IncludeShapeLevel' => 'country',
        ];

        foreach (self::GEOCODE_ADDITIONAL_DATA_PARAMS as $paramKey) {
            if (null !== $query->getData($paramKey)) {
                $additionalDataParams[$paramKey] = $query->getData($paramKey);
            }
        }

        return $this->serializeComponents($additionalDataParams);
    }

    /**
     * Add API credentials to query params (v7 only).
     *
     * @param array<string, string> $queryParams
     *
     * @return array<string, string>
     */
    private function withApiCredentials(array $queryParams): array
    {
        if (
            empty($this->apiKey)
            && (empty($this->appId) || empty($this->appCode))
        ) {
            throw new InvalidCredentials('Invalid or missing api key.');
        }

        if (null !== $this->apiKey) {
            $queryParams['apiKey'] = $this->apiKey;
        } else {
            $queryParams['app_id'] = $this->appId;
            $queryParams['app_code'] = $this->appCode;
        }

        return $queryParams;
    }

    /**
     * Serialize the component query parameter (v7 only).
     *
     * @param array<string, string> $components
     */
    private function serializeComponents(array $components): string
    {
        return implode(';', array_map(function ($name, $value) {
            return sprintf('%s,%s', $name, $value);
        }, array_keys($components), $components));
    }
}
