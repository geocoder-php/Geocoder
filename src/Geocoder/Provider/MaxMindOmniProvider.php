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
class MaxMindCityIspOrgProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://geoip.maxmind.com/e?l=%s&i=%s';

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
            throw new UnsupportedException('The MaxMindOmniProvider does not support street addresses.');
        }

        // This API does not support IPv6
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedException('The MaxMindOmniProvider does not support IPv6 addresses.');
        }

        if ($address === '127.0.0.1') {
            return $this->getLocalhostDefaults();
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $this->apiKey, $address);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The MaxMindOmniProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'maxmind_omni';
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

        $data = explode(',', $content);

        if (in_array($data[23], array('INVALID_LICENSE_KEY', 'LICENSE_REQUIRED'))) {
            throw new InvalidCredentialsException('API Key provided is not valid.');
        } elseif ($data[23] == 'IP_NOT_FOUND') {
            throw new NoResultException('Could not retrieve informations for the ip address provided.');
        }

        return array_merge($this->getDefaults(), array(
            'countryCode' => '' === $data[0] ? null : $data[0],
            'country'     => '' === $data[1] ? null : $data[1],
            'regionCode'  => '' === $data[2] ? null : $data[2],
            'region'      => '' === $data[3] ? null : $data[3],
            'city'        => '' === $data[4] ? null : $data[4],
            'latitude'    => '' === $data[5] ? null : $data[5],
            'longitude'   => '' === $data[6] ? null : $data[6],
            'timezone'    => '' === $data[9] ? null : $data[9],
            'zipcode'     => '' === $data[11] ? null : $data[11],
        ));
    }
}