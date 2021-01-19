<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\GraphHopper;

use Geocoder\Collection;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Psr\Http\Client\ClientInterface;

/**
 * @author Gary Gale <gary@vicchi.org>
 */
final class GraphHopper extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'https://graphhopper.com/api/1/geocode?q=%s&key=%s&locale=%s&limit=%d';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'https://graphhopper.com/api/1/geocode?reverse=true&point=%f,%f&key=%s&locale=%s&limit=%d';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param ClientInterface $client an HTTP adapter
     * @param string          $apiKey an API key
     */
    public function __construct(ClientInterface $client, string $apiKey)
    {
        if (empty($apiKey)) {
            throw new InvalidCredentials('No API key provided.');
        }

        $this->apiKey = $apiKey;
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
            throw new UnsupportedOperation('The GraphHopper provider does not support IP addresses, only street addresses.');
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address), $this->apiKey, $query->getLocale(), $query->getLimit());

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

        $url = sprintf(self::REVERSE_ENDPOINT_URL, $latitude, $longitude, $this->apiKey, $query->getLocale(), $query->getLimit());

        return $this->executeQuery($url);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'graphhopper';
    }

    /**
     * @param $url
     *
     * @return Collection
     */
    private function executeQuery(string $url): AddressCollection
    {
        $content = $this->getUrlContents($url);

        $json = json_decode($content, true);

        if (!isset($json['hits'])) {
            return new AddressCollection([]);
        }

        $locations = $json['hits'];

        if (empty($locations)) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($locations as $location) {
            $bounds = [
                'east' => null,
                'north' => null,
                'west' => null,
                'south' => null,
            ];
            if (isset($location['extent'])) {
                $bounds = [
                    'east' => $location['extent'][0],
                    'north' => $location['extent'][1],
                    'west' => $location['extent'][2],
                    'south' => $location['extent'][3],
                ];
            }

            $results[] = Address::createFromArray([
                'providedBy' => $this->getName(),
                'latitude' => $location['point']['lat'],
                'longitude' => $location['point']['lng'],
                'bounds' => $bounds,
                'streetNumber' => isset($location['housenumber']) ? $location['housenumber'] : null,
                'streetName' => isset($location['street']) ? $location['street'] : null,
                'locality' => isset($location['city']) ? $location['city'] : null,
                'postalCode' => isset($location['postcode']) ? $location['postcode'] : null,
                'country' => isset($location['country']) ? $location['country'] : null,
            ]);
        }

        return new AddressCollection($results);
    }
}
