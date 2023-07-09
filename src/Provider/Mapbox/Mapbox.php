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
use Geocoder\Model\AddressCollection;
use Geocoder\Model\AddressBuilder;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Mapbox\Model\MapboxAddress;
use Geocoder\Provider\Provider;
use Psr\Http\Client\ClientInterface;

final class Mapbox extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL_SSL = 'https://api.mapbox.com/geocoding/v5/%s/%s.json';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL_SSL = 'https://api.mapbox.com/geocoding/v5/%s/%F,%F.json';

    /**
     * @var string
     */
    const GEOCODING_MODE_PLACES = 'mapbox.places';

    /**
     * @var string
     */
    const GEOCODING_MODE_PLACES_PERMANENT = 'mapbox.places-permanent';

    /**
     * @var array
     */
    const GEOCODING_MODES = [
        self::GEOCODING_MODE_PLACES,
        self::GEOCODING_MODE_PLACES_PERMANENT,
    ];

    /**
     * @var string
     */
    const TYPE_COUNTRY = 'country';

    /**
     * @var string
     */
    const TYPE_REGION = 'region';

    /**
     * @var string
     */
    const TYPE_POSTCODE = 'postcode';

    /**
     * @var string
     */
    const TYPE_DISTRICT = 'district';

    /**
     * @var string
     */
    const TYPE_PLACE = 'place';

    /**
     * @var string
     */
    const TYPE_LOCALITY = 'locality';

    /**
     * @var string
     */
    const TYPE_NEIGHBORHOOD = 'neighborhood';

    /**
     * @var string
     */
    const TYPE_ADDRESS = 'address';

    /**
     * @var string
     */
    const TYPE_POI = 'poi';

    /**
     * @var string
     */
    const TYPE_POI_LANDMARK = 'poi.landmark';

    /**
     * @var array
     */
    const TYPES = [
        self::TYPE_COUNTRY,
        self::TYPE_REGION,
        self::TYPE_POSTCODE,
        self::TYPE_DISTRICT,
        self::TYPE_PLACE,
        self::TYPE_LOCALITY,
        self::TYPE_NEIGHBORHOOD,
        self::TYPE_ADDRESS,
        self::TYPE_POI,
        self::TYPE_POI_LANDMARK,
    ];

    const DEFAULT_TYPE = self::TYPE_ADDRESS;

    /**
     * @var ClientInterface
     */
    private $client;

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
     * @param string|null     $country
     * @param string          $geocodingMode
     */
    public function __construct(
        ClientInterface $client,
        string $accessToken,
        string $country = null,
        string $geocodingMode = self::GEOCODING_MODE_PLACES
    ) {
        parent::__construct($client);

        if (!in_array($geocodingMode, self::GEOCODING_MODES)) {
            throw new InvalidArgument('The Mapbox geocoding mode should be either mapbox.places or mapbox.places-permanent.');
        }

        $this->client = $client;
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

        $url = sprintf(self::GEOCODE_ENDPOINT_URL_SSL, $this->geocodingMode, rawurlencode($query->getText()));

        $urlParameters = [];
        if ($query->getBounds()) {
            // Format is "minLon,minLat,maxLon,maxLat"
            $urlParameters['bbox'] = sprintf(
                '%s,%s,%s,%s',
                $query->getBounds()->getWest(),
                $query->getBounds()->getSouth(),
                $query->getBounds()->getEast(),
                $query->getBounds()->getNorth()
            );
        }

        if (null !== $locationType = $query->getData('location_type')) {
            $urlParameters['types'] = is_array($locationType) ? implode(',', $locationType) : $locationType;
        } else {
            $urlParameters['types'] = self::DEFAULT_TYPE;
        }

        if (null !== $fuzzyMatch = $query->getData('fuzzy_match')) {
            $urlParameters['fuzzyMatch'] = $fuzzyMatch ? 'true' : 'false';
        }

        if ($urlParameters) {
            $url .= '?'.http_build_query($urlParameters);
        }

        return $this->fetchUrl($url, $query->getLimit(), $query->getLocale(), $query->getData('country', $this->country));
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinate = $query->getCoordinates();
        $url = sprintf(
            self::REVERSE_ENDPOINT_URL_SSL,
            $this->geocodingMode,
            $coordinate->getLongitude(),
            $coordinate->getLatitude()
        );

        if (null !== $locationType = $query->getData('location_type')) {
            $urlParameters['types'] = is_array($locationType) ? implode(',', $locationType) : $locationType;
        } else {
            $urlParameters['types'] = self::DEFAULT_TYPE;
        }

        if ($urlParameters) {
            $url .= '?'.http_build_query($urlParameters);
        }

        return $this->fetchUrl($url, $query->getLimit(), $query->getLocale(), $query->getData('country', $this->country));
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'mapbox';
    }

    /**
     * @param string      $url
     * @param int         $limit
     * @param string|null $locale
     * @param string|null $country
     *
     * @return string query with extra params
     */
    private function buildQuery(string $url, int $limit, string $locale = null, string $country = null): string
    {
        $parameters = array_filter([
            'country' => $country,
            'language' => $locale,
            'limit' => $limit,
            'access_token' => $this->accessToken,
        ]);

        $separator = parse_url($url, PHP_URL_QUERY) ? '&' : '?';

        return $url.$separator.http_build_query($parameters);
    }

    /**
     * @param string      $url
     * @param int         $limit
     * @param string|null $locale
     * @param string|null $country
     *
     * @return AddressCollection
     */
    private function fetchUrl(string $url, int $limit, string $locale = null, string $country = null): AddressCollection
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
     * @param AddressBuilder $builder
     * @param string         $type    Component type
     * @param array          $value   The component value
     */
    private function updateAddressComponent(AddressBuilder $builder, string $type, array $value)
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
     * @param string $url
     * @param string $content
     *
     * @return array
     */
    private function validateResponse(string $url, $content): array
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
     * @param AddressBuilder $builder
     * @param array          $result
     */
    private function parseCoordinates(AddressBuilder $builder, array $result)
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
