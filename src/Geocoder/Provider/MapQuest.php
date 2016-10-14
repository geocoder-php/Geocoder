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
use Http\Client\HttpClient;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class MapQuest extends AbstractHttpProvider implements Provider
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
    private $licensed;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param HttpClient $client   An HTTP adapter.
     * @param string     $apiKey   An API key.
     * @param bool       $licensed True to use MapQuest's licensed endpoints, default is false to use the open endpoints (optional).
     */
    public function __construct(HttpClient $client, $apiKey, $licensed = false)
    {
        parent::__construct($client);

        $this->apiKey   = $apiKey;
        $this->licensed = $licensed;
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided.');
        }

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The MapQuest provider does not support IP addresses, only street addresses.');
        }

        if ($this->licensed) {
            $query = sprintf(self::LICENSED_GEOCODE_ENDPOINT_URL, urlencode($address), $this->getLimit(), $this->apiKey);
        } else {
            $query = sprintf(self::OPEN_GEOCODE_ENDPOINT_URL, urlencode($address), $this->getLimit(), $this->apiKey);
        }

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided.');
        }

        if ($this->licensed) {
            $query = sprintf(self::LICENSED_REVERSE_ENDPOINT_URL, $this->apiKey, $latitude, $longitude);
        } else {
            $query = sprintf(self::OPEN_REVERSE_ENDPOINT_URL, $this->apiKey, $latitude, $longitude);
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
     */
    private function executeQuery($query)
    {
        $request = $this->getMessageFactory()->createRequest('GET', $query);
        $content = (string) $this->getHttpClient()->sendRequest($request)->getBody();

        if (empty($content)) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        $json = json_decode($content, true);

        if (!isset($json['results']) || empty($json['results'])) {
            throw new NoResult(sprintf('Could not find results for query "%s".', $query));
        }

        $locations = $json['results'][0]['locations'];

        if (empty($locations)) {
            throw new NoResult(sprintf('Could not find results for query "%s".', $query));
        }

        $results = [];
        foreach ($locations as $location) {
            if ($location['street'] || $location['postalCode'] || $location['adminArea5'] || $location['adminArea4'] || $location['adminArea3'] || $location['adminArea1']) {
                $admins = [];

                if ($location['adminArea3']) {
                    $admins[] = ['name' => $location['adminArea3'], 'level' => 1];
                }

                if ($location['adminArea4']) {
                    $admins[] = ['name' => $location['adminArea4'], 'level' => 2];
                }

                $results[] = array_merge($this->getDefaults(), array(
                    'latitude'    => $location['latLng']['lat'],
                    'longitude'   => $location['latLng']['lng'],
                    'streetName'  => $location['street'] ?: null,
                    'locality'    => $location['adminArea5'] ?: null,
                    'postalCode'  => $location['postalCode'] ?: null,
                    'adminLevels' => $admins,
                    'country'     => $location['adminArea1'] ?: null,
                    'countryCode' => $location['adminArea1'] ?: null,
                ));
            }
        }

        if (empty($results)) {
            throw new NoResult(sprintf('Could not find results for query "%s".', $query));
        }

        return $this->returnResults($results);
    }
}
