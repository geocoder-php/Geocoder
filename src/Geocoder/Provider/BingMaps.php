<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\NoResult;
use Geocoder\Exception\UnsupportedOperation;
use Ivory\HttpAdapter\HttpAdapterInterface;

/**
 * @author David Guyon <dguyon@gmail.com>
 */
class BingMaps extends AbstractHttpProvider implements LocaleAwareProvider
{
    use LocaleTrait;

    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://dev.virtualearth.net/REST/v1/Locations/?maxResults=%d&q=%s&key=%s&incl=ciso2';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://dev.virtualearth.net/REST/v1/Locations/%F,%F?key=%s&incl=ciso2';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter
     * @param string               $apiKey  An API key
     * @param string               $locale  A locale (optional)
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey, $locale = null)
    {
        parent::__construct($adapter);

        $this->apiKey = $apiKey;
        $this->locale = $locale;
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API key provided.');
        }

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The BingMaps provider does not support IP addresses, only street addresses.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $this->getLimit(), urlencode($address), $this->apiKey);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API key provided.');
        }

        $query = sprintf(self::REVERSE_ENDPOINT_URL, $latitude, $longitude, $this->apiKey);

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
     */
    private function executeQuery($query)
    {
        if (null !== $this->getLocale()) {
            $query = sprintf('%s&culture=%s', $query, str_replace('_', '-', $this->getLocale()));
        }

        $content = (string) $this->getAdapter()->get($query)->getBody();

        if (empty($content)) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        $json = json_decode($content);

        if (!isset($json->resourceSets[0]) || !isset($json->resourceSets[0]->resources)) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        $data = (array) $json->resourceSets[0]->resources;

        $results = [];
        foreach ($data as $item) {
            $coordinates = (array) $item->geocodePoints[0]->coordinates;

            $bounds = null;
            if (isset($item->bbox) && is_array($item->bbox) && count($item->bbox) > 0) {
                $bounds = [
                    'south' => $item->bbox[0],
                    'west'  => $item->bbox[1],
                    'north' => $item->bbox[2],
                    'east'  => $item->bbox[3]
                ];
            }

            $streetNumber = null;
            $streetName   = property_exists($item->address, 'addressLine') ? (string) $item->address->addressLine : '';
            $zipcode      = property_exists($item->address, 'postalCode') ? (string) $item->address->postalCode : '';
            $city         = property_exists($item->address, 'locality') ? (string) $item->address->locality: '';
            $country      = property_exists($item->address, 'countryRegion') ? (string) $item->address->countryRegion: '';
            $countryCode  = property_exists($item->address, 'countryRegionIso2') ? (string) $item->address->countryRegionIso2: '';

            $adminLevels = [];

            foreach (['adminDistrict', 'adminDistrict2'] as $i => $property) {
                if (property_exists($item->address, $property)) {
                    $adminLevels[] = ['name' => $item->address->{$property}, 'level' => $i + 1];
                }
            }

            $results[] = array_merge($this->getDefaults(), [
                'latitude'     => $coordinates[0],
                'longitude'    => $coordinates[1],
                'bounds'       => $bounds,
                'streetNumber' => $streetNumber,
                'streetName'   => $streetName,
                'locality'     => empty($city) ? null : $city,
                'postalCode'   => empty($zipcode) ? null : $zipcode,
                'adminLevels'  => $adminLevels,
                'country'      => empty($country) ? null : $country,
                'countryCode'  => empty($countryCode) ? null : $countryCode,
            ]);
        }

        return $this->returnResults($results);
    }
}
