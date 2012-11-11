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

/**
 * @author Andrea Cristaudo <andrea.cristaudo@gmail.com>
 */
class GeoPluginProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://www.geoplugin.net/json.gp?ip=%s';

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The GeoPluginProvider does not support street addresses.');
        }

        if (in_array($address, array('127.0.0.1', '::1'))) {
            return $this->getLocalhostDefaults();
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $address);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The GeoPluginProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'geo_plugin';
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

        if ('' === $content) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $json = json_decode($content, true);

        if (!is_array($json) or !count($json)) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        if (!array_key_exists('geoplugin_status', $json) or (200 !== $json['geoplugin_status'])) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        return array_merge($this->getDefaults(), array(
            'city'        => $this->getJsonKey($json, 'geoplugin_city'),
            'country'     => $this->getJsonKey($json, 'geoplugin_countryName'),
            'countryCode' => $this->getJsonKey($json, 'geoplugin_countryCode'),
            'region'      => $this->getJsonKey($json, 'geoplugin_regionName'),
            'regionCode'  => $this->getJsonKey($json, 'geoplugin_regionCode'),
            'latitude'    => $this->getJsonKey($json, 'geoplugin_latitude'),
            'longitude'   => $this->getJsonKey($json, 'geoplugin_longitude'),
        ));
    }

    /**
     * @param array $json
     * @param string $key
     * @return null|string
     */
    private function getJsonKey($json, $key)
    {
        if (!array_key_exists($key, $json)) {
            return null;
        }

        if ('' === $json[$key]) {
            return null;
        }

        return $json[$key];
    }
}