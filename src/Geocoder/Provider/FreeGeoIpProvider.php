<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\HttpAdapter\HttpAdapterInterface;
use Geocoder\Provider\ProviderInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class FreeGeoIpProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if ('127.0.0.1' === $address) {
            return array(
                'city'      => 'localhost',
                'region'    => 'localhost',
                'country'   => 'localhost'
            );
        }

        $query = sprintf('http://freegeoip.net/json/%s', $address);

        $content = $this->getAdapter()->getContent($query);
        $data = (array)json_decode($content);

        return array(
            'latitude'  => $data['latitude'],
            'longitude' => $data['longitude'],
            'city'      => $data['city'],
            'zipcode'   => $data['zipcode'],
            'region'    => $data['region_name'],
            'country'   => $data['country_name']
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new \RuntimeException('The FreeGeoIpProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'free_geo_ip';
    }
}
