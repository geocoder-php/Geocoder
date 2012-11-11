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
use Geocoder\Exception\NoResultException;
use Geocoder\Exception\UnsupportedException;
use Geocoder\HttpAdapter\HttpAdapterInterface;

/**
 * @author Andrea Cristaudo <andrea.cristaudo@gmail.com>
 */
class GeoIPsProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://api.geoips.com/ip/%s/key/%s/output/json/timezone/true/';

    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @param \Geocoder\HttpAdapter\HttpAdapterInterface $adapter
     * @param string                                     $apiKey
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
            throw new InvalidCredentialsException('No API Key provided.');
        }

        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The GeoIPsProvider does not support street addresses.');
        }

        // This API does not support IPv6
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedException('The GeoIPsProvider does not support IPv6 addresses.');
        }

        if ($address === '127.0.0.1') {
            return $this->getLocalhostDefaults();
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $address, $this->apiKey);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The GeoIPsProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'geoips';
    }

    /**
     * @param  string $query
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $json = json_decode($content, true);
        if (!array_key_exists($json, 'status')) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        } elseif('Bad Request' == $json['status']) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        } elseif('Forbidden' == $json['status']) {
            if ('Limit Exceeded' == $json['message']) {
                throw new NoResultException(sprintf('Could not execute query %s', $query));
            }

            throw new InvalidCredentialsException('API Key provided is not valid.');
        }

        return array_merge($this->getDefaults(), array(
            'country'     => '' === $json['country_name'] ? null : $json['country_name'],
            'countryCode' => '' === $json['country_code'] ? null : $json['country_code'],
            'region'      => '' === $json['region_name']  ? null : $json['region_name'],
            'regionCode'  => '' === $json['region_code']  ? null : $json['region_code'],
            'county'      => '' === $json['county_name']  ? null : $json['county_name'],
            'city'        => '' === $json['city_name']    ? null : $json['city_name'],
            'latitude'    => '' === $json['latitude']     ? null : $json['latitude'],
            'longitude'   => '' === $json['longitude']    ? null : $json['longitude'],
            'timezone'    => '' === $json['timezone']     ? null : $json['timezone'],
        ));
    }
}