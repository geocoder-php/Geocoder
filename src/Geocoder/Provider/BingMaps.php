<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\HttpAdapter\HttpAdapterInterface;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\NoResult;
use Geocoder\Exception\UnsupportedOperation;

/**
 * @author David Guyon <dguyon@gmail.com>
 */
class BingMaps extends AbstractProvider implements LocaleAwareProvider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://dev.virtualearth.net/REST/v1/Locations/?maxResults=%d&q=%s&key=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://dev.virtualearth.net/REST/v1/Locations/%F,%F?key=%s';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $apiKey  An API key.
     * @param string               $locale  A locale (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey, $locale = null)
    {
        parent::__construct($adapter, $locale);

        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided');
        }

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The BingMapsProvider does not support IP addresses.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $this->getMaxResults(), urlencode($address), $this->apiKey);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided');
        }

        $query = sprintf(self::REVERSE_ENDPOINT_URL, $coordinates[0], $coordinates[1], $this->apiKey);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'bing_maps';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    private function executeQuery($query)
    {
        if (null !== $this->getLocale()) {
            $query = sprintf('%s&culture=%s', $query, str_replace('_', '-', $this->getLocale()));
        }

        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $json = json_decode($content);

        if (!isset($json->resourceSets[0]) || !isset($json->resourceSets[0]->resources)) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $data = (array) $json->resourceSets[0]->resources;

        $results = array();

        foreach ($data as $item) {
            $coordinates = (array) $item->geocodePoints[0]->coordinates;

            $bounds = null;
            if (isset($item->bbox) && is_array($item->bbox) && count($item->bbox) > 0) {
                $bounds = array(
                    'south' => $item->bbox[0],
                    'west'  => $item->bbox[1],
                    'north' => $item->bbox[2],
                    'east'  => $item->bbox[3]
                );
            }

            $streetNumber = null;
            $streetName   = property_exists($item->address, 'addressLine') ? (string) $item->address->addressLine : '';
            $zipcode      = property_exists($item->address, 'postalCode') ? (string) $item->address->postalCode : '';
            $city         = property_exists($item->address, 'locality') ? (string) $item->address->locality: '';
            $county       = property_exists($item->address, 'adminDistrict2') ? (string) $item->address->adminDistrict2 : '';
            $region       = property_exists($item->address, 'adminDistrict') ? (string) $item->address->adminDistrict: '';
            $country      = property_exists($item->address, 'countryRegion') ? (string) $item->address->countryRegion: '';

            $results[] = array_merge($this->getDefaults(), array(
                'latitude'     => $coordinates[0],
                'longitude'    => $coordinates[1],
                'bounds'       => $bounds,
                'streetNumber' => $streetNumber,
                'streetName'   => $streetName,
                'locality'     => empty($city) ? null : $city,
                'postalCode'   => empty($zipcode) ? null : $zipcode,
                'county'       => empty($county) ? null : $county,
                'region'       => empty($region) ? null : $region,
                'country'      => empty($country) ? null : $country,
            ));
        }

        return $results;
    }
}
