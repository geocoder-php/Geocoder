<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\InvalidCredentialsException;
use Geocoder\HttpAdapter\HttpAdapterInterface;
use Geocoder\Exception\NoResultException;
use Geocoder\Exception\UnsupportedException;

/**
 * @author mtm <mtm@opencagedata.com>
 */
class OpenCageProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = '%s://api.opencagedata.com/geocode/v1/json?key=%s&query=%s&limit=%d&pretty=1';

    /**
     * @var string
     */
    private $scheme = 'http';

    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $apiKey  An API key.
     * @param bool                 $useSsl  Whether to use an SSL connection (optional).
     * @param string|null          $locale  A locale (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey, $useSsl = false, $locale = null)
    {
        parent::__construct($adapter, $locale);

        $this->apiKey = $apiKey;
        $this->scheme = $useSsl ? 'https' : $this->scheme;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The OpenCageProvider does not support IP addresses.');
        }

        if (null === $this->apiKey) {
            throw new InvalidCredentialsException('No API Key provided.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $this->scheme, $this->apiKey, urlencode($address), $this->getMaxResults() );

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        // latitude, longitude
        $address = sprintf("%f, %f", $coordinates[0], $coordinates[1]);

        return $this->getGeocodedData($address);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'opencage';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {

        if (null !== $this->getLocale()) {
            $query = sprintf('%s&language=%s', $query, $this->getLocale());
        }

        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            throw new NoResultException(sprintf('Could not execute query: %s', $query));
        }

        $json = json_decode($content, true);

        if (!isset($json['total_results']) || $json['total_results'] == 0 ) {
            throw new NoResultException(sprintf('Could not find results for given query: %s', $query));
        }

        $locations = $json['results'];

        if (empty($locations)) {
            throw new NoResultException(sprintf('Could not find results for given query: %s', $query));
        }

        $results = array();



        foreach ($locations as $location) {

            $bounds = null;
            if (isset($location['bounds'])) {
                $bounds = array(
                    'south' => $location['bounds']['southwest']['lat'],
                    'west'  => $location['bounds']['southwest']['lng'],
                    'north' => $location['bounds']['northeast']['lat'],
                    'east'  => $location['bounds']['northeast']['lng'],
                );
            }

            $comp = $location['components'];

            $results[] = array_merge($this->getDefaults(), array(
                'latitude'      => $location['geometry']['lat'],
                'longitude'     => $location['geometry']['lng'],
                'bounds'        => $bounds ?: null,
                'streetNumber'  => isset($comp['house_number']) ? $comp['house_number'] : null,
                'streetName'    => isset($comp['road']        ) ? $comp['road']         : null,
                'cityDistrict'  => isset($comp['suburb']      ) ? $comp['suburb']       : null,
                'city'          => isset($comp['city']        ) ? $comp['city']         : null,
                'zipcode'       => isset($comp['postcode']    ) ? $comp['postcode']     : null,
                'county'        => isset($comp['county']      ) ? $comp['county']       : null,
                'region'        => isset($comp['state']       ) ? $comp['state']        : null,
                'country'       => isset($comp['country']     ) ? $comp['country']      : null,
                'countryCode'   => isset($comp['country_code']) ? strtoupper($comp['country_code']) : null,
                'timezone'      => isset($location['annotations']['timezone']['name']) ? $location['annotations']['timezone']['name'] : null,
            ));
        }

        return $results;
    }
}
