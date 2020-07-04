<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\ArcGISOnline;

use Geocoder\Collection;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author ALKOUM Dorian <baikunz@gmail.com>
 */
final class ArcGISOnline extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/find?text=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/reverseGeocode?location=%F,%F';

    /**
     * @var string
     */
    private $sourceCountry;

    /**
     * @param HttpClient $client        An HTTP adapter
     * @param string     $sourceCountry Country biasing (optional)
     */
    public function __construct(HttpClient $client, ?string $sourceCountry = null)
    {
        parent::__construct($client);

        $this->sourceCountry = $sourceCountry;
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The ArcGISOnline provider does not support IP addresses, only street addresses.');
        }

        // Save a request if no valid address entered
        if (empty($address)) {
            throw new InvalidArgument('Address cannot be empty.');
        }

        $url = sprintf(self::ENDPOINT_URL, urlencode($address));
        $json = $this->executeQuery($url, $query->getLimit());

        // no result
        if (empty($json->locations)) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($json->locations as $location) {
            $data = $location->feature->attributes;

            $coordinates = (array) $location->feature->geometry;
            $streetName = !empty($data->StAddr) ? $data->StAddr : null;
            $streetNumber = !empty($data->AddNum) ? $data->AddNum : null;
            $city = !empty($data->City) ? $data->City : null;
            $zipcode = !empty($data->Postal) ? $data->Postal : null;
            $countryCode = !empty($data->Country) ? $data->Country : null;

            $adminLevels = [];
            foreach (['Region', 'Subregion'] as $i => $property) {
                if (!empty($data->{$property})) {
                    $adminLevels[] = ['name' => $data->{$property}, 'level' => $i + 1];
                }
            }

            $results[] = Address::createFromArray([
                'providedBy' => $this->getName(),
                'latitude' => $coordinates['y'],
                'longitude' => $coordinates['x'],
                'streetNumber' => $streetNumber,
                'streetName' => $streetName,
                'locality' => $city,
                'postalCode' => $zipcode,
                'adminLevels' => $adminLevels,
                'countryCode' => $countryCode,
            ]);
        }

        return new AddressCollection($results);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();

        $url = sprintf(self::REVERSE_ENDPOINT_URL, $longitude, $latitude);
        $json = $this->executeQuery($url, $query->getLimit());

        if (property_exists($json, 'error')) {
            return new AddressCollection([]);
        }

        $data = $json->address;

        $streetName = !empty($data->Address) ? $data->Address : null;
        $city = !empty($data->City) ? $data->City : null;
        $zipcode = !empty($data->Postal) ? $data->Postal : null;
        $region = !empty($data->Region) ? $data->Region : null;
        $county = !empty($data->Subregion) ? $data->Subregion : null;
        $countryCode = !empty($data->CountryCode) ? $data->CountryCode : null;

        return new AddressCollection([
            Address::createFromArray([
                'providedBy' => $this->getName(),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'streetName' => $streetName,
                'locality' => $city,
                'postalCode' => $zipcode,
                'region' => $region,
                'countryCode' => $countryCode,
                'county' => $county,
            ]),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'arcgis_online';
    }

    /**
     * @param string $query
     * @param int    $limit
     *
     * @return string
     */
    private function buildQuery(string $query, int $limit): string
    {
        if (null !== $this->sourceCountry) {
            $query = sprintf('%s&sourceCountry=%s', $query, $this->sourceCountry);
        }

        return sprintf('%s&maxLocations=%d&f=%s&outFields=*', $query, $limit, 'json');
    }

    /**
     * @param string $url
     * @param int    $limit
     *
     * @return \stdClass
     */
    private function executeQuery(string $url, int $limit): \stdClass
    {
        $url = $this->buildQuery($url, $limit);
        $content = $this->getUrlContents($url);
        $json = json_decode($content);

        // API error
        if (!isset($json)) {
            throw InvalidServerResponse::create($url);
        }

        return $json;
    }
}
