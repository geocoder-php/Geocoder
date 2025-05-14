<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Pelias;

use Geocoder\Collection;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Pelias\Model\PeliasAddress;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;

class Pelias extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    protected $root;

    /**
     * @param ClientInterface $client  an HTTP adapter
     * @param string          $root    url of Pelias API
     * @param int             $version version of Pelias API
     */
    public function __construct(ClientInterface $client, string $root, int $version = 1)
    {
        $this->root = sprintf('%s/v%d', rtrim($root, '/'), $version);

        parent::__construct($client);
    }

    /**
     * @param array<string, mixed> $query_data additional query data (API key for instance)
     *
     * @throws \Geocoder\Exception\Exception
     */
    protected function getGeocodeQueryUrl(GeocodeQuery $query, array $query_data = []): string
    {
        $address = $query->getText();

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation(sprintf('The %s provider does not support IP addresses, only street addresses.', $this->getName()));
        }

        $data = [
            'text' => $address,
            'size' => $query->getLimit(),
            'layers' => null !== $query->getData('layers') ? implode(',', $query->getData('layers')) : null,
            'boundary.country' => null !== $query->getData('boundary.country') ? implode(',', $query->getData('boundary.country')) : null,
        ];

        return sprintf('%s/search?%s', $this->root, http_build_query(array_merge($data, $query_data)));
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        return $this->executeQuery($this->getGeocodeQueryUrl($query), $query->getLocale());
    }

    /**
     * @param array<string, mixed> $query_data additional query data (API key for instance)
     *
     * @throws \Geocoder\Exception\Exception
     */
    protected function getReverseQueryUrl(ReverseQuery $query, array $query_data = []): string
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();

        $data = [
            'point.lat' => $latitude,
            'point.lon' => $longitude,
            'size' => $query->getLimit(),
            'layers' => null !== $query->getData('layers') ? implode(',', $query->getData('layers')) : null,
            'boundary.country' => null !== $query->getData('boundary.country') ? implode(',', $query->getData('boundary.country')) : null,
        ];

        return sprintf('%s/reverse?%s', $this->root, http_build_query(array_merge($data, $query_data)));
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        return $this->executeQuery($this->getReverseQueryUrl($query), $query->getLocale());
    }

    public function getName(): string
    {
        return 'pelias';
    }

    protected function executeQuery(string $url, ?string $locale = null): AddressCollection
    {
        $headers = [];
        if (null !== $locale) {
            $headers['Accept-Language'] = $locale;
        }

        $request = $this->createRequest('GET', $url, $headers);
        $content = $this->getParsedResponse($request);
        $json = json_decode($content, true);

        if (isset($json['meta'])) {
            switch ($json['meta']['status_code']) {
                case 401:
                case 403:
                    throw new InvalidCredentials('Invalid or missing api key.');
                case 429:
                    throw new QuotaExceeded('Valid request but quota exceeded.');
            }
        }

        if (
            !isset($json['type'])
            || 'FeatureCollection' !== $json['type']
            || !isset($json['features'])
            || [] === $json['features']
        ) {
            return new AddressCollection([]);
        }

        $features = $json['features'];

        if (empty($features)) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($features as $feature) {
            $builder = new AddressBuilder($this->getName());
            $builder->setCoordinates($feature['geometry']['coordinates'][1], $feature['geometry']['coordinates'][0]);
            $builder->setStreetNumber($feature['properties']['housenumber'] ?? null);
            $builder->setStreetName($this->guessStreetName($feature['properties']));
            $builder->setSubLocality($this->guessSubLocality($feature['properties']));
            $builder->setLocality($this->guessLocality($feature['properties']));
            $builder->setPostalCode($feature['properties']['postalcode'] ?? null);
            $builder->setCountry($feature['properties']['country'] ?? null);
            $builder->setCountryCode(
                isset($feature['properties']['country_code']) ? strtoupper($feature['properties']['country_code']) :
                (isset($feature['properties']['country_a']) ? strtoupper($feature['properties']['country_a']) : null));
            $builder->setTimezone($feature['properties']['timezone'] ?? null);

            if (isset($feature['bbox'])) {
                $builder->setBounds($feature['bbox'][3], $feature['bbox'][2], $feature['bbox'][1], $feature['bbox'][0]);
            }

            $level = 1;
            foreach (['macroregion', 'region', 'macrocounty', 'county', 'locality', 'localadmin', 'borough'] as $component) {
                if (isset($feature['properties'][$component])) {
                    $builder->addAdminLevel($level++, $feature['properties'][$component], $feature['properties'][$component.'_a'] ?? null);
                }
                // Administrative level should be an integer in [1,5].
                if ($level > 5) {
                    break;
                }
            }

            /** @var PeliasAddress $location */
            $location = $builder->build(PeliasAddress::class);

            $location = $location->withId($feature['properties']['id'] ?? null);
            $location = $location->withLayer($feature['properties']['layer'] ?? null);
            $location = $location->withSource($feature['properties']['source'] ?? null);
            $location = $location->withName($feature['properties']['name'] ?? null);
            $location = $location->withConfidence($feature['properties']['confidence'] ?? null);
            $location = $location->withAccuracy($feature['properties']['accuracy'] ?? null);

            $results[] = $location;
        }

        return new AddressCollection($results);
    }

    /**
     * @param array<string, mixed> $components
     *
     * @return string|null
     */
    protected static function guessLocality(array $components)
    {
        $localityKeys = ['locality', 'localadmin', 'city', 'town', 'village', 'hamlet'];

        return self::guessBestComponent($components, $localityKeys);
    }

    /**
     * @param array<string, mixed> $components
     *
     * @return string|null
     */
    protected static function guessSubLocality(array $components)
    {
        $subLocalityKeys = ['neighbourhood', 'city_district'];

        return self::guessBestComponent($components, $subLocalityKeys);
    }

    /**
     * @param array<string, mixed> $components
     *
     * @return string|null
     */
    protected static function guessStreetName(array $components)
    {
        $streetNameKeys = ['road', 'street', 'street_name', 'residential'];

        return self::guessBestComponent($components, $streetNameKeys);
    }

    /**
     * @param array<string, mixed> $components
     * @param string[]             $keys
     *
     * @return string|null
     */
    protected static function guessBestComponent(array $components, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($components[$key]) && !empty($components[$key])) {
                return $components[$key];
            }
        }

        return null;
    }
}
