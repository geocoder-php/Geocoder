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
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\AddressFactory;
use Ivory\HttpAdapter\HttpAdapterInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class GoogleMaps extends AbstractProvider implements LocaleAwareProvider
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://maps.googleapis.com/maps/api/geocode/json?address=%s';

    /**
     * @var string
     */
    const ENDPOINT_URL_SSL = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s';

    /**
     * @var string
     */
    private $region;

    /**
     * @var bool
     */
    private $useSsl;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $locale  A locale (optional).
     * @param string               $region  Region biasing (optional).
     * @param bool                 $useSsl  Whether to use an SSL connection (optional)
     * @param string               $apiKey  Google Geocoding API key (optional)
     */
    public function __construct(HttpAdapterInterface $adapter, $locale = null, $region = null, $useSsl = false, $apiKey = null)
    {
        parent::__construct($adapter, $locale);

        $this->region = $region;
        $this->useSsl = $useSsl;
        $this->apiKey = $apiKey;
    }

    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        // Google API returns invalid data if IP address given
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The GoogleMaps does not support IP addresses.');
        }

        $query = sprintf(
            $this->useSsl ? self::ENDPOINT_URL_SSL : self::ENDPOINT_URL,
            rawurlencode($address)
        );

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        return $this->getGeocodedData(sprintf('%F,%F', $latitude, $longitude));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'google_maps';
    }

    /**
     * @param string $query
     *
     * @return string Query with extra params
     */
    protected function buildQuery($query)
    {
        if (null !== $this->getLocale()) {
            $query = sprintf('%s&language=%s', $query, $this->getLocale());
        }

        if (null !== $this->region) {
            $query = sprintf('%s&region=%s', $query, $this->region);
        }

        if (null !== $this->apiKey) {
            $query = sprintf('%s&key=%s', $query, $this->apiKey);
        }

        return $query;
    }

    /**
     * @param string $query
     *
     * @return array
     */
    private function executeQuery($query)
    {
        $query = $this->buildQuery($query);
        $content = (string) $this->getAdapter()->get($query)->getBody();

        // Throw exception if invalid clientID and/or privateKey used with GoogleMapsBusinessProvider
        if (strpos($content, "Provided 'signature' is not valid for the provided client ID") !== false) {
            throw new InvalidCredentials(sprintf('Invalid client ID / API Key %s', $query));
        }

        if (empty($content)) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $json = json_decode($content);

        // API error
        if (!isset($json)) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        if ('REQUEST_DENIED' === $json->status && 'The provided API key is invalid.' === $json->error_message) {
            throw new InvalidCredentials(sprintf('API key is invalid %s', $query));
        }

        // you are over your quota
        if ('OVER_QUERY_LIMIT' === $json->status) {
            throw new QuotaExceeded(sprintf('Daily quota exceeded %s', $query));
        }

        // no result
        if (!isset($json->results) || !count($json->results) || 'OK' !== $json->status) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $results = array();

        foreach ($json->results as $result) {
            $resultset = $this->getDefaults();

            // update address components
            foreach ($result->address_components as $component) {
                foreach ($component->types as $type) {
                    $this->updateAddressComponent($resultset, $type, $component);
                }
            }

            // update coordinates
            $coordinates = $result->geometry->location;
            $resultset['latitude']  = $coordinates->lat;
            $resultset['longitude'] = $coordinates->lng;

            $resultset['bounds'] = null;
            if (isset($result->geometry->bounds)) {
                $resultset['bounds'] = array(
                    'south' => $result->geometry->bounds->southwest->lat,
                    'west'  => $result->geometry->bounds->southwest->lng,
                    'north' => $result->geometry->bounds->northeast->lat,
                    'east'  => $result->geometry->bounds->northeast->lng
                );
            } elseif ('ROOFTOP' === $result->geometry->location_type) {
                // Fake bounds
                $resultset['bounds'] = array(
                    'south' => $coordinates->lat,
                    'west'  => $coordinates->lng,
                    'north' => $coordinates->lat,
                    'east'  => $coordinates->lng
                );
            }

            $results[] = array_merge($this->getDefaults(), $resultset);
        }

        return $this->returnResult($results);
    }

    /**
     * Update current resultset with given key/value.
     *
     * @param array  $resultset Resultset to update.
     * @param string $type      Component type.
     * @param object $values    The component values;
     *
     * @return array
     */
    private function updateAddressComponent(&$resultset, $type, $values)
    {
        switch ($type) {
            case 'postal_code':
                $resultset['postalCode'] = $values->long_name;
                break;

            case 'locality':
                $resultset['locality'] = $values->long_name;
                break;

            case 'administrative_area_level_2':
                $resultset['county'] = $values->long_name;
                $resultset['countyCode'] = $values->short_name;
                break;

            case 'administrative_area_level_1':
                $resultset['region'] = $values->long_name;
                $resultset['regionCode'] = $values->short_name;
                break;

            case 'country':
                $resultset['country'] = $values->long_name;
                $resultset['countryCode'] = $values->short_name;
                break;

            case 'street_number':
                $resultset['streetNumber'] = $values->long_name;
                break;

            case 'route':
                $resultset['streetName'] = $values->long_name;
                break;

            case 'sublocality':
                $resultset['subLocality'] = $values->long_name;
                break;

            default:
        }

        return $resultset;
    }
}
