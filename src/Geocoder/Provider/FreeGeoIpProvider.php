<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\UnsupportedException;
use Geocoder\Provider\ProviderInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class FreeGeoIpProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://freegeoip.net/json/%s';

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if ('127.0.0.1' === $address) {
            return $this->getLocalhostDefaults();
        }

        $query = sprintf(self::ENDPOINT_URL, $address);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The FreeGeoIpProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'free_geo_ip';
    }

    /**
     * @param string $query
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            return $this->getDefaults();
        }

        $data = (array)json_decode($content);

        if (empty($data)) {
            return $this->getDefaults();
        }

        return array(
            'latitude'      => isset($data['latitude']) ? $data['latitude'] : null,
            'longitude'     => isset($data['longitude']) ? $data['longitude'] : null,
            'city'          => isset($data['city']) ? $data['city'] : null,
            'cityDistrict'  => null,
            'zipcode'       => isset($data['zipcode']) ? $data['zipcode'] : null,
            'region'        => isset($data['region_name']) ? $data['region_name'] : null,
            'regionCode'    => null,
            'country'       => isset($data['country_name']) ? $data['country_name'] : null,
            'countryCode'   => isset($data['country_code']) ? $data['country_code'] : null
        );
    }
}
