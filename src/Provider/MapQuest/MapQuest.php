<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\MapQuest;

use Geocoder\Collection;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Location;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\AdminLevel;
use Geocoder\Model\Bounds;
use Geocoder\Model\Country;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\Provider;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class MapQuest extends AbstractHttpProvider implements Provider
{
    const DATA_KEY_ADDRESS = 'address';

    const KEY_API_KEY = 'key';

    const KEY_LOCATION = 'location';

    const KEY_OUT_FORMAT = 'outFormat';

    const KEY_MAX_RESULTS = 'maxResults';

    const KEY_THUMB_MAPS = 'thumbMaps';

    const KEY_INTL_MODE = 'intlMode';

    const KEY_BOUNDING_BOX = 'boundingBox';

    const KEY_LAT = 'lat';

    const KEY_LNG = 'lng';

    const MODE_5BOX = '5BOX';

    const OPEN_BASE_URL = 'https://open.mapquestapi.com/geocoding/v1/';

    const LICENSED_BASE_URL = 'https://www.mapquestapi.com/geocoding/v1/';

    const GEOCODE_ENDPOINT = 'address';

    const DEFAULT_GEOCODE_PARAMS = [
        self::KEY_LOCATION => '',
        self::KEY_OUT_FORMAT => 'json',
        self::KEY_API_KEY => '',
    ];

    const DEFAULT_GEOCODE_OPTIONS = [
        self::KEY_MAX_RESULTS => 3,
        self::KEY_THUMB_MAPS => false,
    ];

    const REVERSE_ENDPOINT = 'reverse';

    const ADMIN_LEVEL_STATE = 1;

    const ADMIN_LEVEL_COUNTY = 2;

    /**
     * MapQuest offers two geocoding endpoints one commercial (true) and one open (false)
     * More information: http://developer.mapquest.com/web/tools/getting-started/platform/licensed-vs-open.
     *
     * @var bool
     */
    private $licensed;

    /**
     * @var bool
     */
    private $useRoadPosition;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param ClientInterface $client          an HTTP adapter
     * @param string          $apiKey          an API key
     * @param bool            $licensed        true to use MapQuest's licensed endpoints, default is false to use the open endpoints (optional)
     * @param bool            $useRoadPosition true to use nearest point on a road for the entrance, false to use map display position
     */
    public function __construct(ClientInterface $client, string $apiKey, bool $licensed = false, bool $useRoadPosition = false)
    {
        if (empty($apiKey)) {
            throw new InvalidCredentials('No API key provided.');
        }

        $this->apiKey = $apiKey;
        $this->licensed = $licensed;
        $this->useRoadPosition = $useRoadPosition;
        parent::__construct($client);
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $params = static::DEFAULT_GEOCODE_PARAMS;
        $params[static::KEY_API_KEY] = $this->apiKey;

        $options = static::DEFAULT_GEOCODE_OPTIONS;
        $options[static::KEY_MAX_RESULTS] = $query->getLimit();

        $useGetQuery = true;

        $address = $this->extractAddressFromQuery($query);
        if ($address instanceof Location) {
            $params[static::KEY_LOCATION] = $this->mapAddressToArray($address);
            $options[static::KEY_INTL_MODE] = static::MODE_5BOX;
            $useGetQuery = false;
        } else {
            $addressAsText = $query->getText();

            if (!$addressAsText) {
                throw new InvalidArgument('Cannot geocode empty address');
            }

            // This API doesn't handle IPs
            if (filter_var($addressAsText, FILTER_VALIDATE_IP)) {
                throw new UnsupportedOperation('The MapQuest provider does not support IP addresses, only street addresses.');
            }

            $params[static::KEY_LOCATION] = $addressAsText;
        }

        $bounds = $query->getBounds();
        if ($bounds instanceof Bounds) {
            $options[static::KEY_BOUNDING_BOX] = $this->mapBoundsToArray($bounds);
            $useGetQuery = false;
        }

        if ($useGetQuery) {
            $params = $this->addOptionsForGetQuery($params, $options);

            return $this->executeGetQuery(static::GEOCODE_ENDPOINT, $params);
        } else {
            $params = $this->addOptionsForPostQuery($params, $options);

            return $this->executePostQuery(static::GEOCODE_ENDPOINT, $params);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();

        $params = [
            static::KEY_API_KEY => $this->apiKey,
            static::KEY_LAT => $latitude,
            static::KEY_LNG => $longitude,
        ];

        return $this->executeGetQuery(static::REVERSE_ENDPOINT, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'map_quest';
    }

    private function extractAddressFromQuery(GeocodeQuery $query)
    {
        return $query->getData(static::DATA_KEY_ADDRESS);
    }

    private function getUrl($endpoint): string
    {
        if ($this->licensed) {
            $baseUrl = static::LICENSED_BASE_URL;
        } else {
            $baseUrl = static::OPEN_BASE_URL;
        }

        return $baseUrl.$endpoint;
    }

    private function addGetQuery(string $url, array $params): string
    {
        return $url.'?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    private function addOptionsForGetQuery(array $params, array $options): array
    {
        foreach ($options as $key => $value) {
            if (false === $value) {
                $value = 'false';
            } elseif (true === $value) {
                $value = 'true';
            }
            $params[$key] = $value;
        }

        return $params;
    }

    private function addOptionsForPostQuery(array $params, array $options): array
    {
        $params['options'] = $options;

        return $params;
    }

    private function executePostQuery(string $endpoint, array $params)
    {
        $url = $this->getUrl($endpoint);

        $appKey = $params[static::KEY_API_KEY];
        unset($params[static::KEY_API_KEY]);
        $url .= '?key='.$appKey;

        $requestBody = json_encode($params);
        $request = $this->getMessageFactory()->createRequest('POST', $url, [], $requestBody);

        $response = $this->getHttpClient()->sendRequest($request);
        $content = $this->parseHttpResponse($response, $url);

        return $this->parseResponseContent($content);
    }

    /**
     * @param string $url
     *
     * @return AddressCollection
     */
    private function executeGetQuery(string $endpoint, array $params): AddressCollection
    {
        $baseUrl = $this->getUrl($endpoint);
        $url = $this->addGetQuery($baseUrl, $params);

        $content = $this->getUrlContents($url);

        return $this->parseResponseContent($content);
    }

    private function parseResponseContent(string $content): AddressCollection
    {
        $json = json_decode($content, true);

        if (!isset($json['results']) || empty($json['results'])) {
            return new AddressCollection([]);
        }

        $locations = $json['results'][0]['locations'];

        if (empty($locations)) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($locations as $location) {
            if ($location['street'] || $location['postalCode'] || $location['adminArea5'] || $location['adminArea4'] || $location['adminArea3']) {
                $admins = [];

                $state = $location['adminArea3'];
                if ($state) {
                    $code = null;
                    if (2 == strlen($state)) {
                        $code = $state;
                    }
                    $admins[] = [
                        'name' => $state,
                        'code' => $code,
                        'level' => static::ADMIN_LEVEL_STATE,
                    ];
                }

                if ($location['adminArea4']) {
                    $admins[] = ['name' => $location['adminArea4'], 'level' => static::ADMIN_LEVEL_COUNTY];
                }

                $position = $location['latLng'];
                if (!$this->useRoadPosition) {
                    if ($location['displayLatLng']) {
                        $position = $location['displayLatLng'];
                    }
                }

                $results[] = Address::createFromArray([
                    'providedBy' => $this->getName(),
                    'latitude' => $position['lat'],
                    'longitude' => $position['lng'],
                    'streetName' => $location['street'] ?: null,
                    'locality' => $location['adminArea5'] ?: null,
                    'subLocality' => $location['adminArea6'] ?: null,
                    'postalCode' => $location['postalCode'] ?: null,
                    'adminLevels' => $admins,
                    'country' => $location['adminArea1'] ?: null,
                    'countryCode' => $location['adminArea1'] ?: null,
                ]);
            }
        }

        return new AddressCollection($results);
    }

    private function mapAddressToArray(Location $address): array
    {
        $location = [];

        $streetParts = [
            trim($address->getStreetNumber() ?: ''),
            trim($address->getStreetName() ?: ''),
        ];
        $street = implode(' ', array_filter($streetParts));
        if ($street) {
            $location['street'] = $street;
        }

        if ($address->getSubLocality()) {
            $location['adminArea6'] = $address->getSubLocality();
            $location['adminArea6Type'] = 'Neighborhood';
        }

        if ($address->getLocality()) {
            $location['adminArea5'] = $address->getLocality();
            $location['adminArea5Type'] = 'City';
        }

        /** @var AdminLevel $adminLevel */
        foreach ($address->getAdminLevels() as $adminLevel) {
            switch ($adminLevel->getLevel()) {
                case static::ADMIN_LEVEL_STATE:
                    $state = $adminLevel->getCode();
                    if (!$state) {
                        $state = $adminLevel->getName();
                    }
                    $location['adminArea3'] = $state;
                    $location['adminArea3Type'] = 'State';

                    break;
                case static::ADMIN_LEVEL_COUNTY:
                    $county = $adminLevel->getName();
                    $location['adminArea4'] = $county;
                    $location['adminArea4Type'] = 'County';
            }
        }

        $country = $address->getCountry();
        if ($country instanceof Country) {
            $code = $country->getCode();
            if (!$code) {
                $code = $country->getName();
            }
            $location['adminArea1'] = $code;
            $location['adminArea1Type'] = 'Country';
        }

        $postalCode = $address->getPostalCode();
        if ($postalCode) {
            $location['postalCode'] = $address->getPostalCode();
        }

        return $location;
    }

    private function mapBoundsToArray(Bounds $bounds)
    {
        return [
            'ul' => [static::KEY_LAT => $bounds->getNorth(), static::KEY_LNG => $bounds->getWest()],
            'lr' => [static::KEY_LAT => $bounds->getSouth(), static::KEY_LNG => $bounds->getEast()],
        ];
    }

    protected function parseHttpResponse(ResponseInterface $response, string $url): string
    {
        $statusCode = $response->getStatusCode();
        if (401 === $statusCode || 403 === $statusCode) {
            throw new InvalidCredentials();
        } elseif (429 === $statusCode) {
            throw new QuotaExceeded();
        } elseif ($statusCode >= 300) {
            throw InvalidServerResponse::create($url, $statusCode);
        }

        $body = (string) $response->getBody();
        if (empty($body)) {
            throw InvalidServerResponse::emptyResponse($url);
        }

        return $body;
    }
}
