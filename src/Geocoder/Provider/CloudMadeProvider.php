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
    const GEOCODE_ENDPOINT_URL = 'http://geocoding.cloudmade.com/%s/geocoding/v2/find.js?query=%s&distance=closest&return_location=true&results=1';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://geocoding.cloudmade.com/%s/geocoding/v2/find.js?around=%F,%F&object_type=address&return_location=true&results=1';

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

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $this->apiKey, urlencode($address));

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

        $query = sprintf(self::REVERSE_ENDPOINT_URL, $this->apiKey, $coordinates[0], $coordinates[1]);

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

        $json = json_decode($content);
        if (isset($json->found) && $json->found > 0) {
            $data = (array) $json->features[0];
        } else {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $coordinates = (array) $data['centroid']->coordinates;

        $bounds = null;
        if (isset($data['bounds']) && is_array($data['bounds']) && count($data['bounds']) > 0) {
            $bounds = array(
                'south' => $data['bounds'][0][0],
                'west'  => $data['bounds'][0][1],
                'north' => $data['bounds'][1][0],
                'east'  => $data['bounds'][1][1]
            );
        }

        $properties = (array) $data['properties'];

        $streetNumber = null;
        if (isset($properties['addr:housenumber'])) {
            $streetNumber = $properties['addr:housenumber'];
        }

        $streetName = null;
        if (isset($properties['addr:street'])) {
            $streetName = $properties['addr:street'];
        } elseif (isset($properties['name'])) {
            $streetName = $properties['name'];
        } elseif (isset($data['location']->road)) {
            $streetName = $data['location']->road;
        }

        return array_merge($this->getDefaults(), array(
            'latitude'     => $coordinates[0],
            'longitude'    => $coordinates[1],
            'bounds'       => $bounds,
            'streetNumber' => $streetNumber,
            'streetName'   => $streetName,
            'city'         => isset($data['location']->city) ? $data['location']->city : null,
            'zipcode'      => isset($data['location']->zipcode) ? $data['location']->zipcode : null,
            'region'       => isset($data['location']->county) ? $data['location']->county : null,
            'county'       => isset($data['location']->county) ? $data['location']->county : null,
            'country'      => isset($data['location']->country) ? $data['location']->country : null,
        ));
    }
}
