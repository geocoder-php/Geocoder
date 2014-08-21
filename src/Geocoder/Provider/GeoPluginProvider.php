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
            return array($this->getLocalhostDefaults());
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
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (null === $content || '' === $content) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $json = json_decode($content, true);

        if (!is_array($json) || !count($json)) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        if (!array_key_exists('geoplugin_status', $json) || (200 !== $json['geoplugin_status'] && 206 !== $json['geoplugin_status'])) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $data = array_filter($json);

        return array(array_merge($this->getDefaults(), array(
            'city'        => isset($data['geoplugin_city']) ? $data['geoplugin_city'] : null,
            'country'     => isset($data['geoplugin_countryName']) ? $data['geoplugin_countryName'] : null,
            'countryCode' => isset($data['geoplugin_countryCode']) ? $data['geoplugin_countryCode'] : null,
            'region'      => isset($data['geoplugin_regionName']) ? $data['geoplugin_regionName'] : null,
            'regionCode'  => isset($data['geoplugin_regionCode']) ? $data['geoplugin_regionCode'] : null,
            'latitude'    => isset($data['geoplugin_latitude']) ? $data['geoplugin_latitude'] : null,
            'longitude'   => isset($data['geoplugin_longitude']) ? $data['geoplugin_longitude'] : null,
        )));
    }
}
