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
    public const FORWARD_ENDPOINT_URL = 'https://api.mapbox.com/search/geocode/v6/forward';

    /**
     * @var string
     */
    public const REVERSE_ENDPOINT_URL = 'https://api.mapbox.com/search/geocode/v6/reverse';

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
     * Japanese addresses only. The API accepts this type only for Japan-context requests
     * (e.g. with country=jp and language=ja), so it is intentionally not part of TYPES.
     *
     * @var string
     */
    public const TYPE_BLOCK = 'block';

    /**
     * Sub-unit, suite, or lot within a parent address (US only). The API accepts this type
     * only for US-context requests, so it is intentionally not part of TYPES.
     *
     * @var string
     */
    public const TYPE_SECONDARY_ADDRESS = 'secondary_address';

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
     * Query data keys that trigger the v6 Structured Input mode (the query text is not sent).
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
    public const DEFAULT_TYPE = self::TYPE_ADDRESS;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string|null
     */
    private $country;

    /**
     * @var bool
     */
    private $permanent;

    /**
     * @param ClientInterface $client      An HTTP adapter
     * @param string          $accessToken Your Mapbox access token
     * @param string|null     $country     Restrict results to one or more ISO 3166 alpha-2 countries (comma separated)
     * @param bool            $permanent   Store results permanently (v6 `permanent` parameter)
     */
    public function __construct(
        ClientInterface $client,
        string $accessToken,
        ?string $country = null,
        bool $permanent = false,
    ) {
        parent::__construct($client);

        $this->accessToken = $accessToken;
        $this->country = $country;
        $this->permanent = $permanent;
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        // The Mapbox API does not geocode raw IP addresses
        if (filter_var($query->getText(), FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Mapbox provider does not support IP addresses, only street addresses.');
        }

        $parameters = [];

        // v6 Structured Input: typed fields replace the `q` search text
        $structured = $this->structuredInputFields($query);
        if ([] !== $structured) {
            foreach (self::STRUCTURED_INPUT_FIELDS as $field) {
                if (isset($structured[$field])) {
                    $parameters[$field] = $structured[$field];
                }
            }
        } else {
            $parameters['q'] = $query->getText();
        }

        if ($query->getBounds()) {
            // Format is "minLon,minLat,maxLon,maxLat"
            $parameters['bbox'] = sprintf(
                '%s,%s,%s,%s',
                $this->formatCoordinate($query->getBounds()->getWest()),
                $this->formatCoordinate($query->getBounds()->getSouth()),
                $this->formatCoordinate($query->getBounds()->getEast()),
                $this->formatCoordinate($query->getBounds()->getNorth())
            );
        }

        $parameters['types'] = $this->locationTypes($query);

        if (null !== $autocomplete = $query->getData('autocomplete')) {
            $parameters['autocomplete'] = $autocomplete ? 'true' : 'false';
        } elseif ([] !== $structured) {
            // Mapbox recommends autocomplete=false for structured input
            $parameters['autocomplete'] = 'false';
        }

        if (null !== $proximity = $query->getData('proximity')) {
            $parameters['proximity'] = $proximity;
        }

        if (null !== $worldview = $query->getData('worldview')) {
            $parameters['worldview'] = $worldview;
        }

        return $this->fetchUrl(
            self::FORWARD_ENDPOINT_URL,
            $query->getLimit(),
            $query->getLocale(),
            $query->getData('country', $this->country),
            $parameters
        );
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinate = $query->getCoordinates();
        $parameters = [
            'longitude' => $this->formatCoordinate($coordinate->getLongitude()),
            'latitude' => $this->formatCoordinate($coordinate->getLatitude()),
        ];

        $parameters['types'] = $this->locationTypes($query);

        return $this->fetchUrl(
            self::REVERSE_ENDPOINT_URL,
            $query->getLimit(),
            $query->getLocale(),
            $query->getData('country', $this->country),
            $parameters
        );
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
     * @param GeocodeQuery|ReverseQuery $query
     */
    private function locationTypes($query): string
    {
        if (null !== $locationType = $query->getData('location_type')) {
            return is_array($locationType) ? implode(',', $locationType) : $locationType;
        }

        return self::DEFAULT_TYPE;
    }

    /**
     * Format a coordinate with the shortest decimal representation that round-trips the
     * value exactly (a plain (string) cast truncates to the 14 significant digits of the
     * default `precision` ini setting, which would change the request URL).
     */
    private function formatCoordinate(float $value): string
    {
        return (string) json_encode($value);
    }

    public function getName(): string
    {
        return 'mapbox';
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function buildQuery(string $url, int $limit, ?string $locale, ?string $country, array $parameters): string
    {
        if (null !== $country) {
            $parameters['country'] = $country;
        }

        if (null !== $locale) {
            $parameters['language'] = $locale;
        }

        $parameters['limit'] = $limit;

        if ($this->permanent) {
            $parameters['permanent'] = 'true';
        }

        $parameters['access_token'] = $this->accessToken;

        return $url.'?'.http_build_query($parameters);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function fetchUrl(string $url, int $limit, ?string $locale, ?string $country, array $parameters): AddressCollection
    {
        $url = $this->buildQuery($url, $limit, $locale, $country, $parameters);
        $content = $this->getUrlContents($url);
        $json = $this->validateResponse($url, $content);

        // no result
        if (!isset($json['features']) || !count($json['features'])) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($json['features'] as $result) {
            if (!array_key_exists('context', $result)) {
                break;
            }

            $builder = new AddressBuilder($this->getName());
            $this->parseCoordinates($builder, $result);

            // set official Mapbox place id
            if (isset($result['id'])) {
                $builder->setValue('id', $result['id']);
            }

            // set official Mapbox place id
            if (isset($result['text'])) {
                $builder->setValue('street_name', $result['text']);
            }

            // update address components
            foreach ($result['context'] as $component) {
                $this->updateAddressComponent($builder, $component['id'], $component);
            }

            /** @var MapboxAddress $address */
            $address = $builder->build(MapboxAddress::class);
            $address = $address->withId($builder->getValue('id'));
            if (isset($result['address'])) {
                $address = $address->withStreetNumber($result['address']);
            }
            if (isset($result['place_type'])) {
                $address = $address->withResultType($result['place_type']);
            }
            if (isset($result['place_name'])) {
                $address = $address->withFormattedAddress($result['place_name']);
            }
            $address = $address->withStreetName($builder->getValue('street_name'));
            $address = $address->withNeighborhood($builder->getValue('neighborhood'));
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
     * @param string               $type  Component type
     * @param array<string, mixed> $value Component value
     */
    private function updateAddressComponent(AddressBuilder $builder, string $type, array $value): void
    {
        $typeParts = explode('.', $type);
        $type = reset($typeParts);

        switch ($type) {
            case 'postcode':
                $builder->setPostalCode($value['text']);

                break;

            case 'locality':
                $builder->setLocality($value['text']);

                break;

            case 'country':
                $builder->setCountry($value['text']);
                if (isset($value['short_code'])) {
                    $builder->setCountryCode(strtoupper($value['short_code']));
                }

                break;

            case 'neighborhood':
                $builder->setValue($type, $value['text']);

                break;

            case 'place':
                $builder->addAdminLevel(1, $value['text']);
                $builder->setLocality($value['text']);

                break;

            case 'region':
                $code = null;
                if (!empty($value['short_code']) && preg_match('/[A-z]{2}-/', $value['short_code'])) {
                    $code = preg_replace('/[A-z]{2}-/', '', $value['short_code']);
                }
                $builder->addAdminLevel(2, $value['text'], $code);

                break;

            default:
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
