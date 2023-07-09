<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\AzureMaps;

use Geocoder\Collection;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;
use stdClass;

/**
 * @author Max Langerman <max@langerman.io>
 */
class AzureMaps extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_SSL = 'https://atlas.microsoft.com/search/address';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'https://atlas.microsoft.com/search/address/reverse';

    /**
     * @var array
     */
    private $options = [
        'typeahead' => null,
        'limit' => 5,
        'ofs' => null,
        'countrySet' => null,
        'lat' => null,
        'lon' => null,
        'radius' => null,
        'topLeft' => null,
        'btmRight' => null,
        'language' => 'en-US',
        'extendedPostalCodesFor' => null,
        'view' => null,
    ];

    /**
     * @var string
     */
    protected $subscriptionKey;
    /**
     * @var string
     */
    private $format;

    /**
     * AzureMaps constructor.
     */
    public function __construct(
        ClientInterface $client,
        string $subscriptionKey,
        array $options = [],
        string $format = 'json'
    ) {
        parent::__construct($client);

        $this->subscriptionKey = $subscriptionKey;
        $this->format = $format;
        $this->setOptions($options);
    }

    /**
     * @throws \Geocoder\Exception\Exception
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $url = $this->buildGeocodeUrl($query->getText());

        $content = $this->getUrlContents($url);

        $response = $this->validateResponse($content, $url);
        $addresses = $this->formatGeocodeResponse($response);

        return new AddressCollection($addresses);
    }

    /**
     * @throws \Geocoder\Exception\Exception
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $url = $this->buildReverseGeocodeUrl(
            (string) $query->getCoordinates()->getLatitude(),
            (string) $query->getCoordinates()->getLongitude()
        );

        $content = $this->getUrlContents($url);

        $response = $this->validateResponse($content, $url);
        $addresses = $this->formatReverseGeocodeResponse($response);

        return new AddressCollection($addresses);
    }

    /**
     * Returns the provider's name.
     */
    public function getName(): string
    {
        return 'azure_maps';
    }

    /**
     * Returns an array of non null geocode /reverse-geocode options.
     */
    private function setOptions(array $options): void
    {
        $options = array_merge($this->options, $options);

        $this->options = array_filter($options, function ($option) {
            return !is_null($option);
        });
    }

    /**
     * Returns an array of keys to replace.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    private function buildGeocodeUrl(string $query): string
    {
        $url = self::GEOCODE_ENDPOINT_SSL;
        $format = $this->format;
        $subscriptionKey = $this->subscriptionKey;
        $options = http_build_query($this->getOptions());
        $query = urlencode($query);

        return sprintf(
            '%s/%s?api-version=1.0&subscription-key=%s&%s&query=%s',
            $url,
            $format,
            $subscriptionKey,
            $options,
            $query
        );
    }

    private function buildReverseGeocodeUrl(string $latitude, string $longitude): string
    {
        $url = self::REVERSE_ENDPOINT_URL;
        $format = $this->format;
        $subscriptionKey = $this->subscriptionKey;
        $options = http_build_query($this->getOptions());

        return sprintf(
            '%s/%s?api-version=1.0&subscription-key=%s&%s&query=%s,%s',
            $url,
            $format,
            $subscriptionKey,
            $options,
            $latitude,
            $longitude
        );
    }

    private function validateResponse(string $content, string $url): stdClass
    {
        $response = json_decode($content);

        if (!$response) {
            throw InvalidServerResponse::create($url);
        }

        if (isset($response->error)) {
            throw new InvalidServerResponse($response->error->message);
        }

        return $response;
    }

    private function formatGeocodeResponse(stdClass $response): array
    {
        return array_map(function ($result) {
            $builder = new AddressBuilder($this->getName());
            $coordinates = $result->position;
            $bounds = $result->viewport;

            $builder->setCoordinates($coordinates->lat, $coordinates->lon);
            $builder->setBounds(
                $bounds->btmRightPoint->lat,
                $bounds->topLeftPoint->lon,
                $bounds->topLeftPoint->lat,
                $bounds->btmRightPoint->lon
            );
            $builder->setValue('id', $result->id);
            $builder->setValue('type', $result->type);
            $builder->setValue('score', $result->score);

            $builder->setStreetName($result->address->streetName ?? null);
            $builder->setStreetNumber($result->address->streetNumber ?? null);
            $builder->setCountryCode($result->address->countryCode ?? null);
            $builder->setCountry($result->address->country ?? null);
            $builder->setPostalCode($result->address->extendedPostalCode ?? null);

            return $builder->build();
        }, $response->results);
    }

    private function formatReverseGeocodeResponse(stdClass $response): array
    {
        return array_filter(array_map(function ($address) {
            $coordinates = explode(',', $address->position);
            $latitude = array_shift($coordinates);
            $longitude = array_shift($coordinates);

            $bounds = $address->address->boundingBox;
            $southWest = explode(',', $bounds->southWest);
            $south = array_shift($southWest);
            $west = array_shift($southWest);

            $northEast = explode(',', $bounds->northEast);
            $north = array_shift($northEast);
            $east = array_shift($northEast);

            $builder = new AddressBuilder($this->getName());
            $builder->setCoordinates($latitude, $longitude);
            $builder->setBounds(
                $south,
                $west,
                $north,
                $east
            );

            $builder->setStreetName($address->address->streetName ?? null);
            $builder->setStreetNumber($address->address->streetNumber ?? null);
            $builder->setCountryCode($address->address->countryCode ?? null);
            $builder->setCountry($address->address->country ?? null);
            $builder->setPostalCode($address->address->extendedPostalCode ?? null);
            $builder->setLocality($address->address->municipality ?? null);

            return $builder->build();
        }, $response->addresses));
    }
}
