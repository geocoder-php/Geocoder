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
use Geocoder\Exception\NoResultException;
use Geocoder\Exception\InvalidCredentialsException;
use Geocoder\Exception\UnsupportedException;

/**
 * @author David Guyon <dguyon@gmail.com>
 */
class CloudMadeProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://geocoding.cloudmade.com/%s/geocoding/v2/find.js?query=%s&distance=closest&return_location=true&results=%d';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://geocoding.cloudmade.com/%s/geocoding/v2/find.js?around=%F,%F&object_type=address&return_location=true&results=%d';

    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $apiKey  An API key.
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey)
    {
        parent::__construct($adapter, null);

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
            throw new UnsupportedException('The CloudMadeProvider does not support IP addresses.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $this->apiKey, urlencode($address), $this->getMaxResults());

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

        $query = sprintf(self::REVERSE_ENDPOINT_URL, $this->apiKey, $coordinates[0], $coordinates[1], $this->getMaxResults());

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'cloudmade';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (preg_match('/forbidden/i', $content)) {
            throw new InvalidCredentialsException(sprintf('Invalid API Key %s', $this->apiKey));
        }

        if (null === $content) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $json = json_decode($content, true);

        if (isset($json['found']) && $json['found'] > 0) {
            $data = (array) $json['features'];
        } else {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $results = array();

        foreach ($data as $item) {
            $coordinates = (array) $item['centroid']['coordinates'];

            $bounds = null;
            if (isset($item['bounds']) && is_array($item['bounds']) && count($item['bounds']) > 0) {
                $bounds = array(
                    'south' => $item['bounds'][0][0],
                    'west'  => $item['bounds'][0][1],
                    'north' => $item['bounds'][1][0],
                    'east'  => $item['bounds'][1][1]
                );
            }

            $properties = (array) $item['properties'];

            $streetNumber = null;
            if (isset($properties['addr:housenumber'])) {
                $streetNumber = $properties['addr:housenumber'];
            }

            $streetName = null;
            if (isset($properties['addr:street'])) {
                $streetName = $properties['addr:street'];
            } elseif (isset($properties['name'])) {
                $streetName = $properties['name'];
            } elseif (isset($item['location']['road'])) {
                $streetName = $item['location']['road'];
            }

            $results[] = array_merge($this->getDefaults(), array(
                'latitude'     => $coordinates[0],
                'longitude'    => $coordinates[1],
                'bounds'       => $bounds,
                'streetNumber' => $streetNumber,
                'streetName'   => $streetName,
                'city'         => isset($item['location']['city']) ? $item['location']['city'] : null,
                'zipcode'      => isset($item['location']['zipcode']) ? $item['location']['zipcode'] : null,
                'region'       => isset($item['location']['county']) ? $item['location']['county'] : null,
                'county'       => isset($item['location']['county']) ? $item['location']['county'] : null,
                'country'      => isset($item['location']['country']) ? $item['location']['country'] : null,
            ));
        }

        return $results;
    }
}
