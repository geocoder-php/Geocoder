<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GoogleMapsPlaces;

use Geocoder\Collection;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\GoogleMapsPlaces\Model\GooglePlace;
use Geocoder\Provider\GoogleMapsPlaces\Model\OpeningHours;
use Geocoder\Provider\GoogleMapsPlaces\Model\Photo;
use Geocoder\Provider\GoogleMapsPlaces\Model\PlusCode;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\Query;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;
use stdClass;

/**
 * @author atymic <atymicq@gmail.com>
 */
final class GoogleMapsPlaces extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const SEARCH_ENDPOINT_URL_SSL = 'https://maps.googleapis.com/maps/api/place/textsearch/json';

    /**
     * @var string
     */
    const FIND_ENDPOINT_URL_SSL = 'https://maps.googleapis.com/maps/api/place/findplacefromtext/json';

    /**
     * @var string
     */
    const NEARBY_ENDPOINT_URL_SSL = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json';

    /**
     * @var string
     */
    const GEOCODE_MODE_FIND = 'find';

    /**
     * @var string
     */
    const GEOCODE_MODE_SEARCH = 'search';

    /**
     * @var string
     */
    const GEOCODE_MODE_NEARBY = 'nearby';

    /**
     * @var string
     */
    const DEFAULT_GEOCODE_MODE = self::GEOCODE_MODE_FIND;

    /**
     * @var string
     */
    const DEFAULT_FIELDS = 'formatted_address,geometry,icon,name,permanently_closed,photos,place_id,plus_code,types';

    /**
     * @var string|null
     */
    private $apiKey;

    /**
     * @param ClientInterface $client An HTTP adapter
     * @param string          $apiKey Google Maps Places API Key
     */
    public function __construct(ClientInterface $client, string $apiKey)
    {
        parent::__construct($client);

        $this->apiKey = $apiKey;
    }

    /**
     * @param GeocodeQuery $query
     *
     * @return Collection
     *
     * @throws UnsupportedOperation
     * @throws InvalidArgument
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        if (filter_var($query->getText(), FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The GoogleMapsPlaces provider does not support IP addresses');
        }

        if (self::GEOCODE_MODE_FIND === $query->getData('mode', self::DEFAULT_GEOCODE_MODE)) {
            return $this->fetchUrl(self::FIND_ENDPOINT_URL_SSL, $this->buildFindPlaceQuery($query));
        }

        if (self::GEOCODE_MODE_SEARCH === $query->getData('mode', self::DEFAULT_GEOCODE_MODE)) {
            return $this->fetchUrl(self::SEARCH_ENDPOINT_URL_SSL, $this->buildPlaceSearchQuery($query));
        }

        throw new InvalidArgument(sprintf('Mode must be one of `%s, %s`', self::GEOCODE_MODE_FIND, self::GEOCODE_MODE_SEARCH));
    }

    /**
     * @param ReverseQuery $query
     *
     * @return Collection
     *
     * @throws InvalidArgument
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        // for backward compatibility: use SEARCH as default mode (includes formatted_address)
        if (self::GEOCODE_MODE_SEARCH === $query->getData('mode', self::GEOCODE_MODE_SEARCH)) {
            $url = self::SEARCH_ENDPOINT_URL_SSL;
        } else {
            $url = self::NEARBY_ENDPOINT_URL_SSL;
        }

        return $this->fetchUrl($url, $this->buildNearbySearchQuery($query));
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'google_maps_places';
    }

    /**
     * Build query for the find place API.
     *
     * @param GeocodeQuery $geocodeQuery
     *
     * @return array
     */
    private function buildFindPlaceQuery(GeocodeQuery $geocodeQuery): array
    {
        $query = [
            'input' => $geocodeQuery->getText(),
            'inputtype' => 'textquery',
            'fields' => self::DEFAULT_FIELDS,
        ];

        if (null !== $geocodeQuery->getLocale()) {
            $query['language'] = $geocodeQuery->getLocale();
        }

        // If query has bounds, set location bias to those bounds
        if (null !== $bounds = $geocodeQuery->getBounds()) {
            $query['locationbias'] = sprintf(
                'rectangle:%s,%s|%s,%s',
                $bounds->getSouth(),
                $bounds->getWest(),
                $bounds->getNorth(),
                $bounds->getEast()
            );
        }

        if (null !== $geocodeQuery->getData('fields')) {
            $query['fields'] = $geocodeQuery->getData('fields');
        }

        return $query;
    }

    /**
     * Build query for the place search API.
     *
     * @param GeocodeQuery $geocodeQuery
     *
     * @return array
     */
    private function buildPlaceSearchQuery(GeocodeQuery $geocodeQuery): array
    {
        $query = [
            'query' => $geocodeQuery->getText(),
        ];

        if (null !== $geocodeQuery->getLocale()) {
            $query['language'] = $geocodeQuery->getLocale();
        }

        $query = $this->applyDataFromQuery($geocodeQuery, $query, [
            'region',
            'type',
            'opennow',
            'minprice',
            'maxprice',
        ]);

        if (null !== $geocodeQuery->getData('location') && null !== $geocodeQuery->getData('radius')) {
            $query['location'] = (string) $geocodeQuery->getData('location');
            $query['radius'] = (int) $geocodeQuery->getData('radius');
        }

        return $query;
    }

    /**
     * Build query for the nearby search api.
     *
     * @param ReverseQuery $reverseQuery
     *
     * @return array
     */
    private function buildNearbySearchQuery(ReverseQuery $reverseQuery): array
    {
        // for backward compatibility: use SEARCH as default mode (includes formatted_address)
        $mode = $reverseQuery->getData('mode', self::GEOCODE_MODE_SEARCH);

        $query = [
            'location' => sprintf(
                '%s,%s',
                $reverseQuery->getCoordinates()->getLatitude(),
                $reverseQuery->getCoordinates()->getLongitude()
            ),
            'rankby' => 'prominence',
        ];

        if (null !== $reverseQuery->getLocale()) {
            $query['language'] = $reverseQuery->getLocale();
        }

        $validParameters = [
            'keyword',
            'type',
            'name',
            'minprice',
            'maxprice',
            'name',
            'opennow',
            'radius',
        ];

        if (self::GEOCODE_MODE_NEARBY === $mode) {
            $validParameters[] = 'rankby';
        }

        $query = $this->applyDataFromQuery($reverseQuery, $query, $validParameters);

        if (self::GEOCODE_MODE_NEARBY === $mode) {
            // mode:nearby, rankby:prominence, parameter:radius
            if ('prominence' === $query['rankby'] && !isset($query['radius'])) {
                throw new InvalidArgument('`radius` is required to be set in the Query data for Reverse Geocoding when ranking by prominence');
            }

            // mode:nearby, rankby:distance, parameter:type/keyword/name
            if ('distance' === $query['rankby']) {
                if (isset($query['radius'])) {
                    unset($query['radius']);
                }

                $requiredParameters = array_intersect(['keyword', 'type', 'name'], array_keys($query));

                if (1 !== count($requiredParameters)) {
                    throw new InvalidArgument('One of `type`, `keyword`, `name` is required to be set in the Query data for Reverse Geocoding when ranking by distance');
                }
            }
        }

        if (self::GEOCODE_MODE_SEARCH === $mode) {
            // mode:search, parameter:type

            if (!isset($query['type'])) {
                throw new InvalidArgument('`type` is required to be set in the Query data for Reverse Geocoding when using search mode');
            }
        }

        return $query;
    }

    /**
     * @param Query $query
     * @param array $request
     * @param array $keys
     *
     * @return array
     */
    private function applyDataFromQuery(Query $query, array $request, array $keys)
    {
        foreach ($keys as $key) {
            if (null === $query->getData($key)) {
                continue;
            }

            $request[$key] = $query->getData($key);
        }

        return $request;
    }

    /**
     * @param string $url
     * @param array  $query
     *
     * @return AddressCollection
     */
    private function fetchUrl(string $url, array $query): AddressCollection
    {
        $query['key'] = $this->apiKey;

        $url = sprintf('%s?%s', $url, http_build_query($query));

        $content = $this->getUrlContents($url);
        $json = $this->validateResponse($url, $content);

        if (empty($json->candidates) && empty($json->results) || 'OK' !== $json->status) {
            return new AddressCollection([]);
        }

        $results = [];

        $apiResults = isset($json->results) ? $json->results : $json->candidates;

        foreach ($apiResults as $result) {
            $builder = new AddressBuilder($this->getName());
            $this->parseCoordinates($builder, $result);

            if (isset($result->place_id)) {
                $builder->setValue('id', $result->place_id);
            }

            /** @var GooglePlace $address */
            $address = $builder->build(GooglePlace::class);
            $address = $address->withId($builder->getValue('id'));

            if (isset($result->name)) {
                $address = $address->withName($result->name);
            }

            if (isset($result->formatted_address)) {
                $address = $address->withFormattedAddress($result->formatted_address);
            }

            if (isset($result->vicinity)) {
                $address = $address->withVicinity($result->vicinity);
            }

            if (isset($result->types)) {
                $address = $address->withType($result->types);
            }

            if (isset($result->icon)) {
                $address = $address->withIcon($result->icon);
            }

            if (isset($result->plus_code)) {
                $address = $address->withPlusCode(new PlusCode(
                    $result->plus_code->global_code,
                    $result->plus_code->compound_code
                ));
            }

            if (isset($result->photos)) {
                $address = $address->withPhotos(Photo::getPhotosFromResult($result->photos));
            }

            if (isset($result->price_level)) {
                $address = $address->withPriceLevel($result->price_level);
            }

            if (isset($result->rating)) {
                $address = $address->withRating((float) $result->rating);
            }

            if (isset($result->formatted_phone_number)) {
                $address = $address->withFormattedPhoneNumber($result->formatted_phone_number);
            }

            if (isset($result->international_phone_number)) {
                $address = $address->withInternationalPhoneNumber($result->international_phone_number);
            }

            if (isset($result->website)) {
                $address = $address->withWebsite($result->website);
            }

            if (isset($result->opening_hours)) {
                $address = $address->withOpeningHours(OpeningHours::fromResult($result->opening_hours));
            }

            if (isset($result->permanently_closed)) {
                $address = $address->setPermanentlyClosed();
            }

            $results[] = $address;
        }

        return new AddressCollection($results);
    }

    /**
     * Decode the response content and validate it to make sure it does not have any errors.
     *
     * @param string $url
     * @param string $content
     *
     * @return \StdClass
     *
     * @throws InvalidCredentials
     * @throws InvalidServerResponse
     * @throws QuotaExceeded
     */
    private function validateResponse(string $url, $content): StdClass
    {
        $json = json_decode($content);

        // API error
        if (!isset($json)) {
            throw InvalidServerResponse::create($url);
        }

        if ('INVALID_REQUEST' === $json->status) {
            throw new InvalidArgument(sprintf('Invalid Request %s', $url));
        }

        if ('REQUEST_DENIED' === $json->status && 'The provided API key is invalid.' === $json->error_message) {
            throw new InvalidCredentials(sprintf('API key is invalid %s', $url));
        }

        if ('REQUEST_DENIED' === $json->status) {
            throw new InvalidServerResponse(sprintf('API access denied. Request: %s - Message: %s', $url, $json->error_message));
        }

        if ('OVER_QUERY_LIMIT' === $json->status) {
            throw new QuotaExceeded(sprintf('Daily quota exceeded %s', $url));
        }

        return $json;
    }

    /**
     * Parse coordinates and bounds.
     *
     * @param AddressBuilder $builder
     * @param StdClass       $result
     */
    private function parseCoordinates(AddressBuilder $builder, StdClass $result)
    {
        $coordinates = $result->geometry->location;
        $builder->setCoordinates($coordinates->lat, $coordinates->lng);

        if (isset($result->geometry->viewport)) {
            $builder->setBounds(
                $result->geometry->viewport->southwest->lat,
                $result->geometry->viewport->southwest->lng,
                $result->geometry->viewport->northeast->lat,
                $result->geometry->viewport->northeast->lng
            );
        }
    }
}
