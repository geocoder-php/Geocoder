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
 * @author Tudor Matei <tudor@tudormatei.com>
 */
class TelizeProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOIP_ENDPOINT_URL = 'http://www.telize.com/geoip';

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $locale  A locale (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $locale = null)
    {
        parent::__construct($adapter, $locale);
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The TelizeProvider does not support street addresses.');
        }

        if (in_array($address, array('127.0.0.1', '::1'))) {
            return array($this->getLocalhostDefaults());
        }

        $query = self::GEOIP_ENDPOINT_URL . '/' . rawurlencode($address);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The TelizeProvider is not able to do reverse geocoding.');
    }

    /**
     * @param string $query
     *
     * @throws \Geocoder\Exception\NoResultException
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (null === $content || '' === $content) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $data = json_decode($content, true);

        if (isset($data['code']) && $data['code'] == 401) {
            throw new NoResultException('Input string is not a valid IP address.');
        }

        if (!isset($data['ip'])) {
            throw new NoResultException('Invalid result returned by provider.');
        }

        return array(array_merge($this->getDefaults(), array(
            'latitude'      => isset($data['latitude']) ? $data['latitude'] : null,
            'longitude'     => isset($data['longitude']) ? $data['longitude'] : null,
            'city'          => isset($data['city']) ? $data['city'] : null,
            'zipcode'       => isset($data['postal_code']) ? $data['postal_code'] : null,
            'region'        => isset($data['region']) ? $data['region'] : null,
            'regionCode'    => isset($data['region_code']) ? $data['region_code'] : null,
            'country'       => isset($data['country']) ? $data['country'] : null,
            'countryCode'   => isset($data['country_code']) ? $data['country_code'] : null,
            'timezone'      => isset($data['timezone']) ? $data['timezone'] : null,
        )));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'telize';
    }

}
