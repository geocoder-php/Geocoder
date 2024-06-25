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
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\Query;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;

/**
 * @author Sébastien Barré <sebastien@sheub.eu>
 */
final class Here extends AbstractHttpProvider
{
    /**
     * @var string
     */
    public const GEOCODE_ENDPOINT_URL = 'https://geocode.search.hereapi.com/v1/geocode';

    /**
     * @var string
     */
    public const REVERSE_ENDPOINT_URL = 'https://revgeocode.search.hereapi.com/v1/revgeocode';

    /**
     * @var string[]
     */
    public const GEOCODE_QUALIFIED_QUERY_PARAMS = [
        'houseNumber',
        'street',
        'city',
        'district',
        'county',
        'state',
        'country',
        'postalCode',
    ];

    /**
     * @var string[]
     */
    public const GEOCODE_TYPES = [
        'address',
        'area',
        'city',
        'houseNumber',
        'postalCode',
        'street',
    ];

    public const GEOCODE_POLITICAL_VIEWS = [
        'ARG',
        'EGY',
        'IND',
        'KEN',
        'MAR',
        'PAK',
        'RUS',
        'SDN',
        'SRB',
        'SUR',
        'SYR',
        'TUR',
        'TZA',
        'URY',
        'VNM',
    ];

    public const GEOCODE_SHOW_PARAMS = [
        'countryInfo',
        'parsing',
        'postalCodeDetails',
        'streetInfo',
        'tz',
    ];

    public const REV_GEOCODE_SHOW_PARAMS = [
        'countryInfo',
        'postalCodeDetails',
        'streetInfo',
        'tz',
    ];

    public const GEOCODE_SHOW_MAP_REFERENCE_PARAMS = [
        'adminIds',
        'cmVersion',
        'pointAddress',
        'segments',
    ];

    public const GEOCODE_SHOW_NAV_ATTRIBUTES = [
        'access',
        'functionalClass',
        'physical',
    ];

    private ?string $appId;
    private ?string $appCode;
    private ?string $apiKey = null;
    private bool $useTestHeader;

    /**
     * @param ClientInterface $client        an HTTP adapter
     * @param string|null     $appId         an App ID
     * @param string|null     $appCode       an App code
     * @param bool            $useTestHeader use testing header
     */
    public function __construct(ClientInterface $client, ?string $appId = null, ?string $appCode = null, bool $useTestHeader = false)
    {
        $this->appId = $appId;
        $this->appCode = $appCode;
        $this->useTestHeader = $useTestHeader;

        parent::__construct($client);
    }

    public static function createUsingApiKey(ClientInterface $client, string $apiKey, bool $useTestHeader = false): self
    {
        $newClient = new self($client, null, null, $useTestHeader);
        $newClient->apiKey = $apiKey;

        return $newClient;
    }

    /**
     * @throws \JsonException
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        // This API doesn't handle IPs
        if (filter_var($query->getText(), FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Here provider does not support IP addresses, only street addresses.');
        }

        $queryParams = $this->withApiCredentials([]);

        if ($queryString = $query->getText()) {
            $queryParams['q'] = $queryString;
        }

        foreach ($this::GEOCODE_QUALIFIED_QUERY_PARAMS as $param) {
            if ($data = $query->getData($param)) {
                if (!\array_key_exists('qq', $queryParams)) {
                    $queryParams['qq'] = '';
                } else {
                    $queryParams['qq'] .= ';';
                }
                $queryParams['qq'] .= $param.'='.$data;
            }
        }

        if (!\array_key_exists('q', $queryParams) && !\array_key_exists('qq', $queryParams)) {
            throw new InvalidArgument('Query q or Qualified Query qq is required');
        }

        if ($center = $query->getData('centerOn')) {
            if (\count($center) > 2) {
                throw new InvalidArgument(sprintf('Expected a set of 2 coordinates got %s', \count($center)));
            }

            $queryParams['at'] = $center['lat'].','.$center['lon'];
        }

        if ($countries = $query->getData('in')) {
            switch (true) {
                case \is_array($countries):
                    $queryParams['in'] = 'countryCode:'.\implode(',', $countries);
                    break;
                case \is_string($countries):
                    $queryParams['in'] = 'countryCode:'.$countries;
                    break;
                default:
                    throw new InvalidArgument('Expected a string or an array of country codes');
            }
        }

        if ($showParams = $query->getData('show')) {
            if (!is_array($showParams)) {
                throw new InvalidArgument('Show param(s) must be an array');
            }

            if (\count(\array_intersect($showParams, $this::GEOCODE_SHOW_PARAMS)) < \count($showParams)) {
                throw new InvalidArgument(sprintf('Show param(s) "%s" are invalid', implode(',', array_diff($showParams, $this::GEOCODE_SHOW_PARAMS))));
            }

            $queryParams['show'] = \implode(',', $showParams);
        }

        $queryParams = $this->buildGenericQueryPararms($query, $queryParams);

        return $this->executeQuery(sprintf('%s?%s', $this->getBaseUrl($query), http_build_query($queryParams)), $query->getLimit());
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        $queryParams = $this->withApiCredentials([]);
        $coordinates = $query->getCoordinates();

        if ($circle = $query->getData('in')) {
            if (!(\array_key_exists('radius', $circle) && $circle['radius'])
            ) {
                throw new InvalidArgument('In requires radius');
            }

            $queryParams['in'] = 'circle:'.$coordinates->getLatitude().','.$coordinates->getLongitude().';r='.$circle['radius'];
        } else {
            $queryParams['at'] = sprintf('%s,%s', $coordinates->getLatitude(), $coordinates->getLongitude());
        }

        if ($bearing = $query->getData('bearing')) {
            if (0 > $bearing || $bearing > 359) {
                throw new InvalidArgument('Bearing must be between 0 and 359 degrees');
            }
            $queryParams['bearing'] = $bearing;
        }

        if ($showParams = $query->getData('show')) {
            if (!is_array($showParams)) {
                throw new InvalidArgument('Show param(s) must be an array');
            }

            if (\count(\array_intersect($showParams, $this::REV_GEOCODE_SHOW_PARAMS)) < \count($showParams)) {
                throw new InvalidArgument(sprintf('Show param(s) "%s" are invalid', implode(',', array_diff($showParams, $this::GEOCODE_SHOW_PARAMS))));
            }

            $queryParams['show'] = \implode(',', $showParams);
        }

        $queryParams = $this->buildGenericQueryPararms($query, $queryParams);

        return $this->executeQuery(sprintf('%s?%s', $this->getBaseUrl($query), http_build_query($queryParams)), $query->getLimit());
    }

    /**
     * @throws \JsonException
     */
    private function executeQuery(string $url, int $limit): Collection
    {
        // X-OLP-Testing header keeps search relevance from being affected.
        $headers = [];
        if ($this->useTestHeader) {
            $headers['X-OLP-Testing'] = 'true';
        }

        $response = $this->createRequest('GET', $url, $headers);

        $content = $this->getParsedResponse($response);

        $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (isset($json['error'])) {
            switch ($json['error']) {
                case 'InvalidInputData':
                    throw new InvalidArgument('Input parameter validation failed.');
                case 'QuotaExceeded':
                    throw new QuotaExceeded('Valid request but quota exceeded.');
                case 'Unauthorized':
                    throw new InvalidCredentials('Invalid or missing api key.');
            }
        }

        if (empty($json['items'])) {
            return new AddressCollection([]);
        }

        $locations = $json['items'];

        $results = [];

        foreach ($locations as $location) {
            $builder = new AddressBuilder($this->getName());
            $coordinates = $location['position'];
            $builder->setCoordinates($coordinates['lat'], $coordinates['lng']);
            $bounds = $location['mapView'];

            $builder->setBounds($bounds['south'], $bounds['west'], $bounds['north'], $bounds['east']);
            $builder->setStreetNumber($location['address']['houseNumber'] ?? null);
            $builder->setStreetName($location['address']['street'] ?? null);
            $builder->setPostalCode($location['address']['postalCode'] ?? null);
            $builder->setLocality($location['address']['city'] ?? null);
            $builder->setSubLocality($location['address']['district'] ?? null);
            $builder->setCountryCode($location['address']['countryCode'] ?? null);
            $builder->setCountry($location['address']['countryName'] ?? null);
            $builder->setTimezone($location['timeZone']['name'] ?? null);

            $additionalData = [
                'countryInfo' => $location['countryInfo'] ?? null,
                'parsing' => $location['parsing'] ?? null,
                'streetInfo' => $location['streetInfo'] ?? null,
                'postalCodeDetails' => $location['postalCodeDetails'] ?? null,
                'mapReferences' => $location['mapReferences'] ?? null,
                'navigationAttributes' => $location['navigationAttributes'] ?? null,
            ];

            /** @var HereAddress $address */
            $address = $builder->build(HereAddress::class);
            $address = $address->withLocationId($location['id'] ?? null);
            $address = $address->withLocationType($location['resultType']);
            $address = $address->withAdditionalData($additionalData);
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

    /**
     * Add API credentials to query params.
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

    public function getBaseUrl(Query $query): string
    {
        if ($query instanceof ReverseQuery) {
            return self::REVERSE_ENDPOINT_URL;
        }

        return self::GEOCODE_ENDPOINT_URL;
    }

    /**
     * @param array<string,string> $queryParams
     *
     * @return array<string,string>
     */
    private function buildGenericQueryPararms(Query $query, array $queryParams): array
    {
        if ($limit = $query->getData('limit')) {
            if (!\is_int($limit) || $limit < 0 || $limit > 100) {
                throw new InvalidArgument(sprintf('%s is not a valid value', $limit));
            }

            $queryParams['limit'] = $limit;
        }

        if ($types = $query->getData('types')) {
            foreach ($types as $type) {
                if (!\in_array($type, $this::GEOCODE_TYPES, true)) {
                    throw new InvalidArgument(sprintf('"%s" is not a valid type', $type));
                }
            }

            $queryParams['types'] = \implode(',', $types);
        }

        if ($locale = $query->getLocale()) {
            $queryParams['lang'] = $locale;
        }

        if ($showMapReferencesParams = $query->getData('showMapReferences')) {
            if (!is_array($showMapReferencesParams)) {
                throw new InvalidArgument('Show map reference preference(s) must be an array');
            }

            if (
                \count(\array_intersect($showMapReferencesParams, $this::GEOCODE_SHOW_MAP_REFERENCE_PARAMS))
                < \count($showMapReferencesParams)
            ) {
                throw new InvalidArgument(sprintf('Show map reference param(s) "%s" are invalid', implode(',', array_diff($showMapReferencesParams, $this::GEOCODE_SHOW_MAP_REFERENCE_PARAMS))));
            }

            $queryParams['showMapReferences'] = implode(',', $showMapReferencesParams);
        }

        if ($showNavAttributesParams = $query->getData('showNavAttributes')) {
            if (!is_array($showNavAttributesParams)) {
                throw new InvalidArgument('Show map nav attribute param(s) must be an array');
            }

            if (
                \count(\array_intersect($showNavAttributesParams, $this::GEOCODE_SHOW_NAV_ATTRIBUTES))
                < \count($showNavAttributesParams)
            ) {
                throw new InvalidArgument(sprintf('Show map nav attribute param(s) "%s" are invalid', implode(',', array_diff($showNavAttributesParams, $this::GEOCODE_SHOW_NAV_ATTRIBUTES))));
            }

            $queryParams['showNavAttributes'] = implode(',', $showNavAttributesParams);
        }

        if ($view = $query->getData('politicalView')) {
            if (!\in_array($view, $this::GEOCODE_POLITICAL_VIEWS, true)) {
                throw new InvalidArgument(sprintf('Political view for "%s" is not a supported', $view));
            }

            $queryParams['politicalView'] = $view;
        }

        return $queryParams;
    }
}
