<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\NoResultException;
use Geocoder\Exception\UnsupportedException;
use Geocoder\HttpAdapter\HttpAdapterInterface;

/**
 * @author ALKOUM Dorian <baikunz@gmail.com>
 */
class ArcGISOnlineProvider extends AbstractProvider implements ProviderInterface
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
    protected $sourceCountry = null;

    /**
     * @var string
     */
    protected $protocol = 'http';

    /**
     * @param HttpAdapterInterface $adapter       An HTTP adapter.
     * @param string               $sourceCountry Country biasing (optional).
     * @param bool                 $useSsl        Whether to use an SSL connection (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $sourceCountry = null, $useSsl = false)
    {
        parent::__construct($adapter);

        $this->sourceCountry = $sourceCountry;
        $this->protocol = $useSsl ? 'https' : 'http';
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The ArcGISOnlineProvider does not support IP addresses.');
        }

        // Save a request if no valid address entered
        if (empty($address)) {
            throw new NoResultException('Invalid address.');
        }

        $query = sprintf(self::ENDPOINT_URL, $this->protocol, urlencode($address));

        $json = $this->executeQuery($query);

        // no result
        if (empty($json->locations)) {
            throw new NoResultException(sprintf('No results found for query %s', $query));
        }

        $results = array();

        foreach ($json->locations as $location) {
            $data = $location->feature->attributes;

            $coordinates  = (array) $location->feature->geometry;
            $streetName   = !empty($data->Match_addr) ? $data->Match_addr : null;
            $streetNumber = !empty($data->AddNum) ? $data->AddNum : null;
            $city         = !empty($data->City) ? $data->City : null;
            $zipcode      = !empty($data->Postal) ? $data->Postal : null;
            $region       = !empty($data->Region) ? $data->Region : null;
            $county       = !empty($data->Subregion) ? $data->Subregion : null;
            $countryCode  = !empty($data->Country) ? $data->Country : null;

            $results[] = array_merge($this->getDefaults(), array(
                'latitude'     => $coordinates['y'],
                'longitude'    => $coordinates['x'],
                'streetNumber' => $streetNumber,
                'streetName'   => $streetName,
                'city'         => $city,
                'zipcode'      => $zipcode,
                'region'       => $region,
                'countryCode'  => $countryCode,
                'county'       => $county,
            ));
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        $query = sprintf(self::REVERSE_ENDPOINT_URL, $this->protocol, $coordinates[1], $coordinates[0]);

        $json = $this->executeQuery($query);

        if (property_exists($json, 'error')) {
            throw new NoResultException(sprintf('No results found for query %s', $query));
        }

        $data = $json->address;

        $streetName   = !empty($data->Address) ? $data->Address : null;
        $city         = !empty($data->City) ? $data->City : null;
        $zipcode      = !empty($data->Postal) ? $data->Postal : null;
        $region       = !empty($data->Region) ? $data->Region : null;
        $county       = !empty($data->Subregion) ? $data->Subregion : null;
        $countryCode  = !empty($data->CountryCode) ? $data->CountryCode : null;

        return array(array_merge($this->getDefaults(), array(
            'latitude'     => $coordinates[0],
            'longitude'    => $coordinates[1],
            'streetName'   => $streetName,
            'city'         => $city,
            'zipcode'      => $zipcode,
            'region'       => $region,
            'countryCode'  => $countryCode,
            'county'       => $county,
        )));
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
     *
     * @return string Query with extra params
     */
    protected function buildQuery($query)
    {
        if (null !== $this->getSourceCountry()) {
            $query = sprintf('%s&sourceCountry=%s', $query, $this->getSourceCountry());
        }

        $query = sprintf('%s&maxLocations=%d', $query, $this->getMaxResults());
        $query = sprintf('%s&f=%s', $query, 'json'); // set format to json
        $query = sprintf('%s&outFields=*', $query); // Get all result fields

        return $query;
    }

    /**
     * Executes a query
     *
     * @param string $query
     *
     * @throws NoResultException
     *
     * @return \stdClass json object representing the query result
     */
    protected function executeQuery($query)
    {
        $query = $this->buildQuery($query);

        $content = $this->getAdapter()->getContent($query);
        if (null === $content) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $json = json_decode($content);

        // API error
        if (!isset($json)) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        return $json;
    }

    /**
     * Returns the configured source country or null.
     *
     * @return string|null
     */
    protected function getSourceCountry()
    {
        return $this->sourceCountry;
    }
}
