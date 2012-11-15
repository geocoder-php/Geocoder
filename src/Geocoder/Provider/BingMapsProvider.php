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
use Geocoder\Exception\InvalidCredentialsException;
use Geocoder\Exception\NoResultException;
use Geocoder\Exception\UnsupportedException;

/**
 * @author David Guyon <dguyon@gmail.com>
 */
class BingMapsProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://dev.virtualearth.net/REST/v1/Locations/?q=%s&key=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://dev.virtualearth.net/REST/v1/Locations/%F,%F?key=%s';

    /**
     * @var string
     */
    private $apiKey = null;

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
            throw new InvalidCredentialsException('No API Key provided');
        }

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The BingMapsProvider does not support IP addresses.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address), $this->apiKey);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentialsException('No API Key provided');
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
    protected function executeQuery($query)
    {
        if (null !== $this->getLocale()) {
            $query = sprintf('%s&culture=%s', $query, str_replace('_', '-', $this->getLocale()));
        }

        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $json = json_decode($content);

        if (isset($json->resourceSets[0]) && isset($json->resourceSets[0]->resources[0])) {
            $data = (array) $json->resourceSets[0]->resources[0];
        } else {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $coordinates = (array) $data['geocodePoints'][0]->coordinates;

        $bounds = null;
        if (isset($data['bbox']) && is_array($data['bbox']) && count($data['bbox']) > 0) {
            $bounds = array(
                'south' => $data['bbox'][0],
                'west'  => $data['bbox'][1],
                'north' => $data['bbox'][2],
                'east'  => $data['bbox'][3]
            );
        }

        $streetNumber = null;
        $streetName   = property_exists($data['address'], 'addressLine') ? (string) $data['address']->addressLine : '';
        $zipcode      = property_exists($data['address'], 'postalCode') ? (string) $data['address']->postalCode : '';
        $city         = property_exists($data['address'], 'locality') ? (string) $data['address']->locality: '';
        $county       = property_exists($data['address'], 'adminDistrict2') ? (string) $data['address']->adminDistrict2 : '';
        $region       = property_exists($data['address'], 'adminDistrict') ? (string) $data['address']->adminDistrict: '';
        $country      = property_exists($data['address'], 'countryRegion') ? (string) $data['address']->countryRegion: '';

        return array_merge($this->getDefaults(), array(
            'latitude'     => $coordinates[0],
            'longitude'    => $coordinates[1],
            'bounds'       => $bounds,
            'streetNumber' => $streetNumber,
            'streetName'   => $streetName,
            'city'         => empty($city) ? null : $city,
            'zipcode'      => empty($zipcode) ? null : $zipcode,
            'county'       => empty($county) ? null : $county,
            'region'       => empty($region) ? null : $region,
            'country'      => empty($country) ? null : $country,
        ));
    }
}
