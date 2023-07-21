<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\OpenCage;

use Geocoder\Collection;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\OpenCage\Model\OpenCageAddress;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Psr\Http\Client\ClientInterface;

/**
 * @author mtm <mtm@opencagedata.com>
 */
final class OpenCage extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    public const GEOCODE_ENDPOINT_URL = 'https://api.opencagedata.com/geocode/v1/json?key=%s&query=%s&limit=%d&pretty=1';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param ClientInterface $client an HTTP adapter
     * @param string          $apiKey an API key
     */
    public function __construct(ClientInterface $client, string $apiKey)
    {
        if (empty($apiKey)) {
            throw new InvalidCredentials('No API key provided.');
        }

        $this->apiKey = $apiKey;
        parent::__construct($client);
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The OpenCage provider does not support IP addresses, only street addresses.');
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL, $this->apiKey, urlencode($address), $query->getLimit());
        if (null !== $countryCode = $query->getData('countrycode')) {
            $url = sprintf('%s&countrycode=%s', $url, $countryCode);
        }
        if (null !== $bounds = $query->getBounds()) {
            $url = sprintf('%s&bounds=%s,%s,%s,%s', $url, $bounds->getWest(), $bounds->getSouth(), $bounds->getEast(), $bounds->getNorth());
        }
        if (null !== $proximity = $query->getData('proximity')) {
            $url = sprintf('%s&proximity=%s', $url, $proximity);
        }

        return $this->executeQuery($url, $query->getLocale());
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $address = sprintf('%f, %f', $coordinates->getLatitude(), $coordinates->getLongitude());

        $geocodeQuery = GeocodeQuery::create($address);
        if (null !== $locale = $query->getLocale()) {
            $geocodeQuery = $geocodeQuery->withLocale($query->getLocale());
        }

        return $this->geocodeQuery($geocodeQuery);
    }

    public function getName(): string
    {
        return 'opencage';
    }

    /**
     * @throws \Geocoder\Exception\Exception
     */
    private function executeQuery(string $url, string $locale = null): AddressCollection
    {
        if (null !== $locale) {
            $url = sprintf('%s&language=%s', $url, $locale);
        }

        $content = $this->getUrlContents($url);
        $json = json_decode($content, true);

        // https://geocoder.opencagedata.com/api#codes
        if (isset($json['status'])) {
            switch ($json['status']['code']) {
                case 400:
                    throw new InvalidArgument('Invalid request (a required parameter is missing).');
                case 402:
                    throw new QuotaExceeded('Valid request but quota exceeded.');
                case 403:
                    throw new InvalidCredentials('Invalid or missing api key.');
            }
        }

        if (!isset($json['total_results']) || 0 == $json['total_results']) {
            return new AddressCollection([]);
        }

        $locations = $json['results'];

        if (empty($locations)) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($locations as $location) {
            $builder = new AddressBuilder($this->getName());
            $this->parseCoordinates($builder, $location);

            $components = $location['components'];
            $annotations = $location['annotations'];

            $this->parseAdminsLevels($builder, $components);
            $this->parseCountry($builder, $components);
            $builder->setLocality($this->guessLocality($components));
            $builder->setSubLocality($this->guessSubLocality($components));
            $builder->setStreetNumber(isset($components['house_number']) ? $components['house_number'] : null);
            $builder->setStreetName($this->guessStreetName($components));
            $builder->setPostalCode(isset($components['postcode']) ? $components['postcode'] : null);
            $builder->setTimezone(isset($annotations['timezone']['name']) ? $annotations['timezone']['name'] : null);

            /** @var OpenCageAddress $address */
            $address = $builder->build(OpenCageAddress::class);
            $address = $address->withMGRS(isset($annotations['MGRS']) ? $annotations['MGRS'] : null);
            $address = $address->withMaidenhead(isset($annotations['Maidenhead']) ? $annotations['Maidenhead'] : null);
            $address = $address->withGeohash(isset($annotations['geohash']) ? $annotations['geohash'] : null);
            $address = $address->withWhat3words(isset($annotations['what3words'], $annotations['what3words']['words']) ? $annotations['what3words']['words'] : null);
            $address = $address->withFormattedAddress($location['formatted']);

            $results[] = $address;
        }

        return new AddressCollection($results);
    }

    private function parseCoordinates(AddressBuilder $builder, array $location)
    {
        $builder->setCoordinates($location['geometry']['lat'], $location['geometry']['lng']);

        $bounds = [
            'south' => null,
            'west' => null,
            'north' => null,
            'east' => null,
        ];

        if (isset($location['bounds'])) {
            $bounds = [
                'south' => $location['bounds']['southwest']['lat'],
                'west' => $location['bounds']['southwest']['lng'],
                'north' => $location['bounds']['northeast']['lat'],
                'east' => $location['bounds']['northeast']['lng'],
            ];
        }

        $builder->setBounds(
            $bounds['south'],
            $bounds['west'],
            $bounds['north'],
            $bounds['east']
        );
    }

    private function parseAdminsLevels(AddressBuilder $builder, array $components)
    {
        if (isset($components['state'])) {
            $stateCode = isset($components['state_code']) ? $components['state_code'] : null;
            $builder->addAdminLevel(1, $components['state'], $stateCode);
        }

        if (isset($components['county'])) {
            $builder->addAdminLevel(2, $components['county']);
        }
    }

    private function parseCountry(AddressBuilder $builder, array $components)
    {
        if (isset($components['country'])) {
            $builder->setCountry($components['country']);
        }

        if (isset($components['country_code'])) {
            $builder->setCountryCode(\strtoupper($components['country_code']));
        }
    }

    /**
     * @return string|null
     */
    protected function guessLocality(array $components)
    {
        $localityKeys = ['city', 'town', 'municipality', 'village', 'hamlet', 'locality', 'croft'];

        return $this->guessBestComponent($components, $localityKeys);
    }

    /**
     * @return string|null
     */
    protected function guessStreetName(array $components)
    {
        $streetNameKeys = ['road', 'footway', 'street', 'street_name', 'residential', 'path', 'pedestrian', 'road_reference', 'road_reference_intl'];

        return $this->guessBestComponent($components, $streetNameKeys);
    }

    /**
     * @return string|null
     */
    protected function guessSubLocality(array $components)
    {
        $subLocalityKeys = ['neighbourhood', 'suburb', 'city_district', 'district', 'quarter', 'houses', 'subdivision'];

        return $this->guessBestComponent($components, $subLocalityKeys);
    }

    /**
     * @return string|null
     */
    protected function guessBestComponent(array $components, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($components[$key]) && !empty($components[$key])) {
                return $components[$key];
            }
        }

        return null;
    }
}
