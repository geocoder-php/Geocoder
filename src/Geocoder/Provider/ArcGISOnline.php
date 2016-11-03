<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\NoResult;
use Geocoder\Exception\UnsupportedOperation;
use Http\Client\HttpClient;

/**
 * @author ALKOUM Dorian <baikunz@gmail.com>
 */
final class ArcGISOnline extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const ENDPOINT_URL = '%s://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/find?text=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = '%s://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/reverseGeocode?location=%F,%F';

    /**
     * @var string
     */
    private $sourceCountry;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @param HttpClient $client        An HTTP adapter
     * @param string     $sourceCountry Country biasing (optional)
     * @param bool       $useSsl        Whether to use an SSL connection (optional)
     */
    public function __construct(HttpClient $client, $sourceCountry = null, $useSsl = false)
    {
        parent::__construct($client);

        $this->sourceCountry = $sourceCountry;
        $this->protocol      = $useSsl ? 'https' : 'http';
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The ArcGISOnline provider does not support IP addresses, only street addresses.');
        }

        // Save a request if no valid address entered
        if (empty($address)) {
            throw new NoResult('Invalid address.');
        }

        $query = sprintf(self::ENDPOINT_URL, $this->protocol, urlencode($address));
        $json  = $this->executeQuery($query);

        // no result
        if (empty($json->locations)) {
            throw new NoResult(sprintf('No results found for query "%s".', $query));
        }

        $results = [];
        foreach ($json->locations as $location) {
            $data = $location->feature->attributes;

            $coordinates  = (array) $location->feature->geometry;
            $streetName   = !empty($data->Match_addr) ? $data->Match_addr : null;
            $streetNumber = !empty($data->AddNum) ? $data->AddNum : null;
            $city         = !empty($data->City) ? $data->City : null;
            $zipcode      = !empty($data->Postal) ? $data->Postal : null;
            $countryCode  = !empty($data->Country) ? $data->Country : null;

            $adminLevels = [];
            foreach (['Region', 'Subregion'] as $i => $property) {
                if (! empty($data->{$property})) {
                    $adminLevels[] = ['name' => $data->{$property}, 'level' => $i + 1];
                }
            }

            $results[] = array_merge($this->getDefaults(), [
                'latitude'     => $coordinates['y'],
                'longitude'    => $coordinates['x'],
                'streetNumber' => $streetNumber,
                'streetName'   => $streetName,
                'locality'     => $city,
                'postalCode'   => $zipcode,
                'adminLevels'  => $adminLevels,
                'countryCode'  => $countryCode,
            ]);
        }

        return $this->returnResults($results);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        $query = sprintf(self::REVERSE_ENDPOINT_URL, $this->protocol, $longitude, $latitude);
        $json  = $this->executeQuery($query);

        if (property_exists($json, 'error')) {
            throw new NoResult(sprintf('No results found for query "%s".', $query));
        }

        $data = $json->address;

        $streetName   = !empty($data->Address) ? $data->Address : null;
        $city         = !empty($data->City) ? $data->City : null;
        $zipcode      = !empty($data->Postal) ? $data->Postal : null;
        $region       = !empty($data->Region) ? $data->Region : null;
        $county       = !empty($data->Subregion) ? $data->Subregion : null;
        $countryCode  = !empty($data->CountryCode) ? $data->CountryCode : null;

        return $this->returnResults([
            array_merge($this->getDefaults(), [
                'latitude'    => $latitude,
                'longitude'   => $longitude,
                'streetName'  => $streetName,
                'locality'    => $city,
                'postalCode'  => $zipcode,
                'region'      => $region,
                'countryCode' => $countryCode,
                'county'      => $county,
            ])
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'arcgis_online';
    }

    /**
     * @param string $query
     */
    private function buildQuery($query)
    {
        if (null !== $this->sourceCountry) {
            $query = sprintf('%s&sourceCountry=%s', $query, $this->sourceCountry);
        }

        return sprintf('%s&maxLocations=%d&f=%s&outFields=*', $query, $this->getLimit(), 'json');
    }

    /**
     * @param string $query
     */
    private function executeQuery($query)
    {
        $query = $this->buildQuery($query);
        $request = $this->getMessageFactory()->createRequest('GET', $query);
        $content = (string) $this->getHttpClient()->sendRequest($request)->getBody();

        if (empty($content)) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        $json = json_decode($content);

        // API error
        if (!isset($json)) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        return $json;
    }
}
