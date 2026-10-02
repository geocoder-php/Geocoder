<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Mapbox;

use Geocoder\Collection;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Mapbox\Model\MapboxAddress;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;

final class Mapbox extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    public const GEOCODE_ENDPOINT_URL_SSL = 'https://api.mapbox.com/search/geocode/v6/forward';

    /**
     * @var string
     */
    public const REVERSE_ENDPOINT_URL_SSL = 'https://api.mapbox.com/search/geocode/v6/reverse';

    /**
     * @var string
     */
    public const GEOCODING_MODE_PLACES = 'mapbox.places';

    /**
     * @var string
     */
    public const GEOCODING_MODE_PLACES_PERMANENT = 'mapbox.places-permanent';

    /**
     * @var string[]
     */
    public const GEOCODING_MODES = [
        self::GEOCODING_MODE_PLACES,
        self::GEOCODING_MODE_PLACES_PERMANENT,
    ];

    /**
     * @var string
     */
    public const TYPE_COUNTRY = 'country';

    /**
     * @var string
     */
    public const TYPE_REGION = 'region';

    /**
     * @var string
     */
    public const TYPE_POSTCODE = 'postcode';

    /**
     * @var string
     */
    public const TYPE_DISTRICT = 'district';

    /**
     * @var string
     */
    public const TYPE_PLACE = 'place';

    /**
     * @var string
     */
    public const TYPE_LOCALITY = 'locality';

    /**
     * @var string
     */
    public const TYPE_NEIGHBORHOOD = 'neighborhood';

    /**
     * @var string
     */
    public const TYPE_ADDRESS = 'address';

    /**
     * @var string
     */
    public const TYPE_STREET = 'street';

    /**
     * @var string[]
     */
    public const TYPES = [
        self::TYPE_COUNTRY,
        self::TYPE_REGION,
        self::TYPE_POSTCODE,
        self::TYPE_DISTRICT,
        self::TYPE_PLACE,
        self::TYPE_LOCALITY,
        self::TYPE_NEIGHBORHOOD,
        self::TYPE_STREET,
        self::TYPE_ADDRESS,
    ];

    /**
     * @var string
     */
    public const DEFAULT_TYPE = self::TYPE_ADDRESS;

    /**
     * v6 Structured Input fields. When one or more of these is set on the
     * query, the typed fields are sent and the `q` search text is dropped.
     *
     * @var string[]
     */
    public const STRUCTURED_INPUT_FIELDS = [
        'address_line1',
        'address_number',
        'street',
        'block',
        'place',
        'region',
        'postcode',
        'locality',
        'neighborhood',
    ];

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string|null
     */
    private $country;

    /**
     * @var string
     */
    private $geocodingMode;

    /**
     * @param ClientInterface $client        An HTTP adapter
     * @param string          $accessToken   Your Mapbox access token
     * @param string|null     $country       Restrict the results to one or more ISO 3166 alpha-2 countries
     * @param string          $geocodingMode One of the GEOCODING_MODES; v6 expresses the permanent mode
     *                                       through the `permanent` request parameter
     */
    public function __construct(
        ClientInterface $client,
        string $accessToken,
        ?string $country = null,
        string $geocodingMode = self::GEOCODING_MODE_PLACES,
    ) {
        parent::__construct($client);

        if (!in_array($geocodingMode, self::GEOCODING_MODES)) {
            throw new InvalidArgument('The Mapbox geocoding mode should be either mapbox.places or mapbox.places-permanent.');
        }

        $this->accessToken = $accessToken;
        $this->country = $country;
        $this->geocodingMode = $geocodingMode;
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        // Mapbox API returns invalid data if IP address given
        // This API doesn't handle IPs
        if (filter_var($query->getText(), FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Mapbox provider does not support IP addresses, only street addresses.');
        }

        $url = self::GEOCODE_ENDPOINT_URL_SSL;

        $urlParameters = [];

        // v6 Structured Input: the typed fields replace the `q` search text
        $structured = $this->structuredInputFields($query);
        if ([] !== $structured) {
            foreach (self::STRUCTURED_INPUT_FIELDS as $field) {
                if (isset($structured[$field])) {
                    $urlParameters[$field] = $structured[$field];
                }
            }
        } else {
            $urlParameters['q'] = $query->getText();
        }

        if ($query->getBounds()) {
            // Format is "minLon,minLat,maxLon,maxLat"
            $urlParameters['bbox'] = sprintf(
                '%s,%s,%s,%s',
                $this->formatCoordinate($query->getBounds()->getWest()),
                $this->formatCoordinate($query->getBounds()->getSouth()),
                $this->formatCoordinate($query->getBounds()->getEast()),
                $this->formatCoordinate($query->getBounds()->getNorth())
            );
        }

        if (null !== $locationType = $query->getData('location_type')) {
            $urlParameters['types'] = is_array($locationType) ? implode(',', $locationType) : $locationType;
        } else {
            $urlParameters['types'] = self::DEFAULT_TYPE;
        }

        // v6 has no `fuzzyMatch` parameter; `autocomplete` is its closest replacement
        if (null !== $autocomplete = $query->getData('autocomplete')) {
            $urlParameters['autocomplete'] = $autocomplete ? 'true' : 'false';
        } elseif ([] !== $structured) {
            // Mapbox recommends disabling autocomplete for structured input
            $urlParameters['autocomplete'] = 'false';
        }

        if (count($urlParameters) > 0) {
            $url .= '?'.http_build_query($urlParameters);
        }

        return $this->fetchUrl($url, $query->getLimit(), $query->getLocale(), $query->getData('country', $this->country));
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinate = $query->getCoordinates();
        $url = self::REVERSE_ENDPOINT_URL_SSL;

        $urlParameters = [
            'longitude' => $this->formatCoordinate($coordinate->getLongitude()),
            'latitude' => $this->formatCoordinate($coordinate->getLatitude()),
        ];

        if (null !== $locationType = $query->getData('location_type')) {
            $urlParameters['types'] = is_array($locationType) ? implode(',', $locationType) : $locationType;
        } else {
            $urlParameters['types'] = self::DEFAULT_TYPE;
        }

        if (count($urlParameters) > 0) {
            $url .= '?'.http_build_query($urlParameters);
        }

        return $this->fetchUrl($url, $query->getLimit(), $query->getLocale(), $query->getData('country', $this->country));
    }

    public function getName(): string
    {
        return 'mapbox';
    }

    /**
     * @return string query with extra params
     */
    private function buildQuery(string $url, int $limit, ?string $locale = null, ?string $country = null): string
    {
        $parameters = array_filter([
            'country' => $country,
            'language' => $locale,
        ]);

        $parameters['limit'] = $limit;

        if (self::GEOCODING_MODE_PLACES_PERMANENT === $this->geocodingMode) {
            // v6 replaced the "mapbox.places-permanent" mode with a `permanent` parameter
            $parameters['permanent'] = 'true';
        }

        $parameters['access_token'] = $this->accessToken;

        $separator = parse_url($url, PHP_URL_QUERY) ? '&' : '?';

        return $url.$separator.http_build_query($parameters);
    }

    private function fetchUrl(string $url, int $limit, ?string $locale = null, ?string $country = null): AddressCollection
    {
        $url = $this->buildQuery($url, $limit, $locale, $country);
        $content = $this->getUrlContents($url);
        $json = $this->validateResponse($url, $content);

        // no result
        if (!isset($json['features']) || !count($json['features'])) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($json['features'] as $result) {
            $builder = new AddressBuilder($this->getName());
            $this->parseCoordinates($builder, $result);

            // in v6 the feature details (id, name, type, context) live in `properties`
            $properties = $result['properties'] ?? [];

            // set official Mapbox place id
            if (isset($properties['mapbox_id'])) {
                $builder->setValue('id', $properties['mapbox_id']);
            } elseif (isset($result['id'])) {
                $builder->setValue('id', $result['id']);
            }

            // update address components (v6 `context` is an object keyed by feature type)
            foreach ($properties['context'] ?? [] as $type => $component) {
                $this->updateAddressComponent($builder, (string) $type, $component);
            }

            /** @var MapboxAddress $address */
            $address = $builder->build(MapboxAddress::class);
            $address = $address->withId($builder->getValue('id'));

            // street name without the house number
            $streetName = $properties['context']['address']['street_name']
                ?? $properties['context']['street']['name']
                ?? $properties['name']
                ?? null;
            if (null !== $streetName) {
                $address = $address->withStreetName((string) $streetName);
            }

            if (isset($properties['context']['address']['address_number'])) {
                $address = $address->withStreetNumber((string) $properties['context']['address']['address_number']);
            }

            if (isset($properties['feature_type'])) {
                $address = $address->withResultType([(string) $properties['feature_type']]);
            }

            if (isset($properties['full_address'])) {
                $address = $address->withFormattedAddress((string) $properties['full_address']);
            }

            if (isset($properties['context']['neighborhood']['name'])) {
                $address = $address->withNeighborhood((string) $properties['context']['neighborhood']['name']);
            }

            if (isset($properties['match_code']) && is_array($properties['match_code'])) {
                $address = $address->withMatchCode($properties['match_code']);
                if (isset($properties['match_code']['confidence'])) {
                    $address = $address->withMatchConfidence((string) $properties['match_code']['confidence']);
                }
            }

            if (isset($properties['coordinates']['accuracy'])) {
                $address = $address->withAccuracy((string) $properties['coordinates']['accuracy']);
            }

            $results[] = $address;

            if (count($results) >= $limit) {
                break;
            }
        }

        return new AddressCollection($results);
    }

    /**
     * @return array<string, mixed>
     */
    private function structuredInputFields(GeocodeQuery $query): array
    {
        $fields = [];

        foreach (self::STRUCTURED_INPUT_FIELDS as $field) {
            $value = $query->getData($field);
            if (null !== $value && '' !== $value) {
                $fields[$field] = $value;
            }
        }

        return $fields;
    }

    /**
     * Format a coordinate with the shortest representation that round-trips
     * the value exactly (a plain (string) cast would truncate it to the
     * `precision` ini setting).
     */
    private function formatCoordinate(float $value): string
    {
        return (string) json_encode($value);
    }

    /**
     * Update current resultSet with given key/value.
     *
     * @param array<string, mixed> $component
     */
    private function updateAddressComponent(AddressBuilder $builder, string $type, array $component): void
    {
        if (!isset($component['name'])) {
            return;
        }

        switch ($type) {
            case 'postcode':
                $builder->setPostalCode($component['name']);

                break;

            case 'locality':
                $builder->setLocality($component['name']);

                break;

            case 'country':
                $builder->setCountry($component['name']);
                if (isset($component['country_code'])) {
                    $builder->setCountryCode(strtoupper((string) $component['country_code']));
                }

                break;

            case 'place':
                $builder->addAdminLevel(1, $component['name']);
                $builder->setLocality($component['name']);

                break;

            case 'region':
                $code = isset($component['region_code']) ? (string) $component['region_code'] : null;
                $builder->addAdminLevel(2, $component['name'], $code);

                break;

            default:
                // address, street, district: handled elsewhere or not part of the address model
        }
    }

    /**
     * Decode the response content and validate it to make sure it does not have any errors.
     *
     * @return array<string, mixed>
     */
    private function validateResponse(string $url, string $content): array
    {
        $json = json_decode($content, true);

        // API error
        if (!isset($json) || JSON_ERROR_NONE !== json_last_error()) {
            throw InvalidServerResponse::create($url);
        }

        return $json;
    }

    /**
     * Parse coordinats and bounds.
     *
     * @param array<string, mixed> $result
     */
    private function parseCoordinates(AddressBuilder $builder, array $result): void
    {
        $coordinates = $result['geometry']['coordinates'];
        $builder->setCoordinates($coordinates[1], $coordinates[0]);

        if (isset($result['bbox'])) {
            $builder->setBounds(
                $result['bbox'][1],
                $result['bbox'][0],
                $result['bbox'][3],
                $result['bbox'][2]
            );
        }
    }
}
