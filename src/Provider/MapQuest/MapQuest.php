<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\MapQuest;

use Geocoder\Collection;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class MapQuest extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const OPEN_GEOCODE_ENDPOINT_URL = 'https://open.mapquestapi.com/geocoding/v1/address?location=%s&outFormat=json&maxResults=%d&key=%s&thumbMaps=false';

    /**
     * @var string
     */
    const OPEN_REVERSE_ENDPOINT_URL = 'https://open.mapquestapi.com/geocoding/v1/reverse?key=%s&lat=%F&lng=%F';

    /**
     * @var string
     */
    const LICENSED_GEOCODE_ENDPOINT_URL = 'https://www.mapquestapi.com/geocoding/v1/address?location=%s&outFormat=json&maxResults=%d&key=%s&thumbMaps=false';

    /**
     * @var string
     */
    const LICENSED_REVERSE_ENDPOINT_URL = 'https://www.mapquestapi.com/geocoding/v1/reverse?key=%s&lat=%F&lng=%F';

    /**
     * MapQuest offers two geocoding endpoints one commercial (true) and one open (false)
     * More information: http://developer.mapquest.com/web/tools/getting-started/platform/licensed-vs-open.
     *
     * @var bool
     */
    private $licensed;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param HttpClient $client   an HTTP adapter
     * @param string     $apiKey   an API key
     * @param bool       $licensed true to use MapQuest's licensed endpoints, default is false to use the open endpoints (optional)
     */
    public function __construct(HttpClient $client, string $apiKey, bool $licensed = false)
    {
        if (empty($apiKey)) {
            throw new InvalidCredentials('No API key provided.');
        }

        $this->apiKey = $apiKey;
        $this->licensed = $licensed;
        parent::__construct($client);
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The MapQuest provider does not support IP addresses, only street addresses.');
        }

        if ($this->licensed) {
            $url = sprintf(self::LICENSED_GEOCODE_ENDPOINT_URL, urlencode($address), $query->getLimit(), $this->apiKey);
        } else {
            $url = sprintf(self::OPEN_GEOCODE_ENDPOINT_URL, urlencode($address), $query->getLimit(), $this->apiKey);
        }

        return $this->executeQuery($url);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();

        if ($this->licensed) {
            $url = sprintf(self::LICENSED_REVERSE_ENDPOINT_URL, $this->apiKey, $latitude, $longitude);
        } else {
            $url = sprintf(self::OPEN_REVERSE_ENDPOINT_URL, $this->apiKey, $latitude, $longitude);
        }

        return $this->executeQuery($url);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'map_quest';
    }

    /**
     * @param string $url
     *
     * @return AddressCollection
     */
    private function executeQuery(string $url): AddressCollection
    {
        $content = $this->getUrlContents($url);
        $json = json_decode($content, true);

        if (!isset($json['results']) || empty($json['results'])) {
            return new AddressCollection([]);
        }

        $locations = $json['results'][0]['locations'];

        if (empty($locations)) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($locations as $location) {
            if ($location['street'] || $location['postalCode'] || $location['adminArea5'] || $location['adminArea4'] || $location['adminArea3']) {
                $admins = [];

                if ($location['adminArea3']) {
                    $admins[] = ['name' => $location['adminArea3'], 'level' => 1];
                }

                if ($location['adminArea4']) {
                    $admins[] = ['name' => $location['adminArea4'], 'level' => 2];
                }

                $results[] = Address::createFromArray([
                    'providedBy' => $this->getName(),
                    'latitude' => $location['latLng']['lat'],
                    'longitude' => $location['latLng']['lng'],
                    'streetName' => $location['street'] ?: null,
                    'locality' => $location['adminArea5'] ?: null,
                    'postalCode' => $location['postalCode'] ?: null,
                    'adminLevels' => $admins,
                    'country' => $location['adminArea1'] ?: null,
                    'countryCode' => $location['adminArea1'] ?: null,
                ]);
            }
        }

        if (empty($results)) {
            return new AddressCollection([]);
        }

        return new AddressCollection($results);
    }
}
