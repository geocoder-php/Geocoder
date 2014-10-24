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
use Geocoder\HttpAdapter\HttpAdapterInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class MapQuest extends AbstractProvider implements Provider
{
    /**
     * @var string
     */
    const OPEN_GEOCODE_ENDPOINT_URL = 'http://open.mapquestapi.com/geocoding/v1/address?location=%s&outFormat=json&maxResults=%d&key=%s&thumbMaps=false';

    /**
     * @var string
     */
    const OPEN_REVERSE_ENDPOINT_URL = 'http://open.mapquestapi.com/geocoding/v1/reverse?key=%s&lat=%F&lng=%F';

    /**
     * @var string
     */
    const LICENSED_GEOCODE_ENDPOINT_URL = 'http://www.mapquestapi.com/geocoding/v1/address?location=%s&outFormat=json&maxResults=%d&key=%s&thumbMaps=false';

    /**
     * @var string
     */
    const LICENSED_REVERSE_ENDPOINT_URL = 'http://www.mapquestapi.com/geocoding/v1/reverse?key=%s&lat=%F&lng=%F';

    /**
     * MapQuest offers two geocoding endpoints one commercial (true) and one open (false)
     * More information: http://developer.mapquest.com/web/tools/getting-started/platform/licensed-vs-open
     *
     * @var bool
     */
    private $licensed = false;

    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @param HttpAdapterInterface $adapter  An HTTP adapter.
     * @param string               $apiKey   An API key.
     * @param string|null          $locale   A locale (optional).
     * @param bool                 $licensed True to use MapQuest's licensed endpoints, default is false to use the open endpoints (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey, $locale = null, $licensed = false)
    {
        parent::__construct($adapter, $locale);

        $this->apiKey = $apiKey;
        $this->licensed = $licensed;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The MapQuestProvider does not support IP addresses.');
        }

        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided.');
        }

        if ($this->licensed) {
            $query = sprintf(self::LICENSED_GEOCODE_ENDPOINT_URL, urlencode($address), $this->getMaxResults(), $this->apiKey);
        } else {
            $query = sprintf(self::OPEN_GEOCODE_ENDPOINT_URL, urlencode($address), $this->getMaxResults(), $this->apiKey);
        }

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided.');
        }

        if ($this->licensed) {
            $query = sprintf(self::LICENSED_REVERSE_ENDPOINT_URL, $this->apiKey, $coordinates[0], $coordinates[1]);
        } else {
            $query = sprintf(self::OPEN_REVERSE_ENDPOINT_URL, $this->apiKey, $coordinates[0], $coordinates[1]);
        }

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'map_quest';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            throw new NoResult(sprintf('Could not execute query: %s', $query));
        }

        $json = json_decode($content, true);

        if (!isset($json['results']) || empty($json['results'])) {
            throw new NoResult(sprintf('Could not find results for given query: %s', $query));
        }

        $locations = $json['results'][0]['locations'];

        if (empty($locations)) {
            throw new NoResult(sprintf('Could not find results for given query: %s', $query));
        }

        $results = [];

        foreach ($locations as $location) {
            if ($location['street'] || $location['postalCode'] || $location['adminArea5'] || $location['adminArea4'] || $location['adminArea3'] || $location['adminArea1']) {
                $results[] = array_merge($this->getDefaults(), array(
                    'latitude'   => $location['latLng']['lat'],
                    'longitude'  => $location['latLng']['lng'],
                    'streetName' => $location['street'] ?: null,
                    'locality'   => $location['adminArea5'] ?: null,
                    'postalCode' => $location['postalCode'] ?: null,
                    'county'     => $location['adminArea4'] ?: null,
                    'region'     => $location['adminArea3'] ?: null,
                    'country'    => $location['adminArea1'] ?: null,
                ));
            }
        }

        if (empty($results)) {
            throw new NoResult(sprintf('Could not find results for given query: %s', $query));
        }

        return $results;
    }
}
