<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\BingMaps;

use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\AbstractHttpProvider;
use Geocoder\Provider\LocaleAwareGeocoder;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author David Guyon <dguyon@gmail.com>
 */
final class BingMaps extends AbstractHttpProvider implements LocaleAwareGeocoder, Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'https://dev.virtualearth.net/REST/v1/Locations/?maxResults=%d&q=%s&key=%s&incl=ciso2';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'https://dev.virtualearth.net/REST/v1/Locations/%F,%F?key=%s&incl=ciso2';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param HttpClient $client An HTTP adapter
     * @param string     $apiKey An API key
     */
    public function __construct(HttpClient $client, $apiKey)
    {
        parent::__construct($client);

        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API key provided.');
        }

        // This API doesn't handle IPs
        if (filter_var($query->getText(), FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The BingMaps provider does not support IP addresses, only street addresses.');
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL, $query->getLimit(), urlencode($query->getText()), $this->apiKey);

        return $this->executeQuery($url, $query->getLocale(), $query->getLimit());
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API key provided.');
        }

        $coordinates = $query->getCoordinates();
        $url = sprintf(self::REVERSE_ENDPOINT_URL, $coordinates->getLatitude(), $coordinates->getLongitude(), $this->apiKey);

        return $this->executeQuery($url, $query->getLocale(), $query->getLimit());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'bing_maps';
    }

    /**
     * @param string $url
     * @param string $locale
     * @param int    $limit
     *
     * @return \Geocoder\Collection
     */
    private function executeQuery($url, $locale, $limit)
    {
        if (null !== $locale) {
            $url = sprintf('%s&culture=%s', $url, str_replace('_', '-', $locale));
        }

        $request = $this->getMessageFactory()->createRequest('GET', $url);
        $content = (string) $this->getHttpClient()->sendRequest($request)->getBody();

        if (empty($content)) {
            throw InvalidServerResponse::create($url);
        }

        $json = json_decode($content);

        if (!isset($json->resourceSets[0]) || !isset($json->resourceSets[0]->resources)) {
            return new AddressCollection([]);
        }

        $data = (array) $json->resourceSets[0]->resources;

        $results = [];
        foreach ($data as $item) {
            $coordinates = (array) $item->geocodePoints[0]->coordinates;

            $bounds = null;
            if (isset($item->bbox) && is_array($item->bbox) && count($item->bbox) > 0) {
                $bounds = [
                    'south' => $item->bbox[0],
                    'west' => $item->bbox[1],
                    'north' => $item->bbox[2],
                    'east' => $item->bbox[3],
                ];
            }

            $streetNumber = null;
            $streetName = property_exists($item->address, 'addressLine') ? (string) $item->address->addressLine : '';
            $zipcode = property_exists($item->address, 'postalCode') ? (string) $item->address->postalCode : '';
            $city = property_exists($item->address, 'locality') ? (string) $item->address->locality : '';
            $country = property_exists($item->address, 'countryRegion') ? (string) $item->address->countryRegion : '';
            $countryCode = property_exists($item->address, 'countryRegionIso2') ? (string) $item->address->countryRegionIso2 : '';

            $adminLevels = [];

            foreach (['adminDistrict', 'adminDistrict2'] as $i => $property) {
                if (property_exists($item->address, $property)) {
                    $adminLevels[] = ['name' => $item->address->{$property}, 'level' => $i + 1];
                }
            }

            $results[] = array_merge($this->getDefaults(), [
                'latitude' => $coordinates[0],
                'longitude' => $coordinates[1],
                'bounds' => $bounds,
                'streetNumber' => $streetNumber,
                'streetName' => $streetName,
                'locality' => empty($city) ? null : $city,
                'postalCode' => empty($zipcode) ? null : $zipcode,
                'adminLevels' => $adminLevels,
                'country' => empty($country) ? null : $country,
                'countryCode' => empty($countryCode) ? null : $countryCode,
            ]);

            if (count($results) >= $limit) {
                break;
            }
        }

        if (empty($results)) {
            return new AddressCollection([]);
        }

        return $this->returnResults($results);
    }
}
