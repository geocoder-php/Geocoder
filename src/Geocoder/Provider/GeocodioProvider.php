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
 * @author Josh Moody <jgmoody@gmail.com>
 */
class GeocodioProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://api.geocod.io/v1/geocode?q=%s&api_key=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://api.geocod.io/v1/reverse?q=%F,%F&api_key=%s';

    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $apiKey  An API key.
     * @param string               $locale  A locale (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey, $locale = null)
    {
        parent::__construct($adapter, $locale);

        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'geocodio';
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The GeocodioProvider does not support IP addresses.');
        }

        if (null === $this->apiKey) {
            throw new InvalidCredentialsException('No API Key provided.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address), $this->apiKey);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentialsException('No API Key provided.');
        }

        $query = sprintf(self::REVERSE_ENDPOINT_URL, $coordinates[0], $coordinates[1], $this->apiKey);

        return $this->executeQuery($query);
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
            throw new NoResultException(sprintf('Could not execute query: %s', $query));
        }

        $json = json_decode($content, true);

        if (!empty($json['error']) && strtolower($json['error']) == 'invalid api key') {
            throw new InvalidCredentialsException('Invalid API Key');
        } elseif (!empty($json['error'])) {
            throw new NoResultException(sprintf('Error returned from api: %s', $json['error']));
        }

        if (empty($json['results'])) {
            throw new NoResultException(sprintf('Could not find results for given query: %s', $query));
        }

        $locations = $json['results'];

        $results = array();

        $ctr = 0;

        foreach ($locations as $location) {
            $ctr++;

            if ($ctr <= $this->getMaxResults()) {

                $coordinates = $location['location'];
                $address = $location['address_components'];
                $street = $address['street'] ?: null;

                if (!empty($address['suffix'])) {
                    $address['street'] .= ' ' . $address['suffix'];
                }

                $results[] = array_merge($this->getDefaults(), array(

                        'latitude'      => $coordinates['lat'] ?: null,
                        'longitude'     => $coordinates['lng'] ?: null,
                        'streetNumber'  => $address['number'] ?: null,
                        'streetName'    => $address['street'] ?: null,
                        'city'          => $address['city'] ?: null,
                        'zipcode'       => $address['zip'] ?: null,
                        'county'        => $address['county'] ?: null,
                        'region'        => $address['state'] ?: null,
                        'country'       => 'US'
                    ));
            }
        }

        return $results;
    }
}
