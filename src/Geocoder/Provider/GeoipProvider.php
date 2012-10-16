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
 * @see http://php.net/manual/ref.geoip.php
 *
 * @author William Durand <william.durand1@gmail.com>
 */
class GeoipProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * No need to pass a HTTP adapter.
     */
    public function __construct()
    {
        if (!function_exists('geoip_record_by_name')) {
            throw new \RuntimeException('You have to install GeoIP');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (in_array($address, array('127.0.0.1', '::1'))) {
            return $this->getLocalhostDefaults();
        }

        $results = @geoip_record_by_name($address);

        $timezone = null;
        if (isset($results['country_code'])) {
            $timezone = @geoip_time_zone_by_country_and_region($results['country_code'], $results['region']);
        }

        return array(
            'latitude'    => $results['latitude'],
            'longitude'   => $results['longitude'],
            'city'        => $results['city'],
            'zipcode'     => $results['postal_code'],
            'region'      => $results['region'],
            'regionCode'  => $results['region'],
            'country'     => $results['country_name'],
            'countryCode' => $results['country_code'],
            'timezone'    => $timezone,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The GeoipProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'geoip';
    }
}
