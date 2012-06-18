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
use Geocoder\Provider\ProviderInterface;

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
     * @param \Geocoder\HttpAdapter\HttpAdapterInterface $adapter
     * @param string                                     $apiKey
     * @param string                                     $locale
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
            throw new \RuntimeException('No API Key provided');
        }

        if ('127.0.0.1' === $address) {
            return $this->getLocalhostDefaults();
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
            throw new \RuntimeException('No API Key provided');
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
     * @return array
     */
    protected function executeQuery($query)
    {
        if (null !== $this->getLocale()) {
            $query = sprintf('%s&culture=%s', $query, str_replace('_', '-', $this->getLocale()));
        }

        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            return $this->getDefaults();
        }

        $json = json_decode($content);

        if (isset($json->resourceSets[0]) && isset($json->resourceSets[0]->resources[0])) {
            $data = (array) $json->resourceSets[0]->resources[0];
        } else {
            return $this->getDefaults();
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

        return array(
            'latitude'      => $coordinates[0],
            'longitude'     => $coordinates[1],
            'bounds'        => $bounds,
            'streetNumber'  => $streetNumber,
            'streetName'    => $streetName,
            'city'          => empty($city) ? null : $city,
            'zipcode'       => empty($zipcode) ? null : $zipcode,
            'cityDistrict'  => null,
            'county'        => empty($county) ? null : $county,
            'region'        => empty($region) ? null : $region,
            'regionCode'    => null,
            'country'       => empty($country) ? null : $country,
            'countryCode'   => null
        );
    }
}
