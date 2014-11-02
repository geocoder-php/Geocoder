<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\NoResult;
use Geocoder\Exception\ExtensionNotLoaded;
use Geocoder\Exception\UnsupportedOperation;

/**
 * @see http://php.net/manual/ref.geoip.php
 *
 * @author William Durand <william.durand1@gmail.com>
 */
class Geoip extends AbstractProvider implements Provider
{
    public function __construct()
    {
        if (!function_exists('geoip_record_by_name')) {
            throw new ExtensionNotLoaded('You must install the GeoIP extension, see: https://php.net/manual/book.geoip.php.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Geoip provider does not support street addresses, only IPv4 addresses.');
        }

        // This API does not support IPv6
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedOperation('The Geoip provider does not support IPv6 addresses, only IPv4 addresses.');
        }

        if ('127.0.0.1' === $address) {
            return $this->returnResults([ $this->getLocalhostDefaults() ]);
        }

        $results = @geoip_record_by_name($address);

        if (!is_array($results)) {
            throw new NoResult(sprintf('Could not find "%s" IP address in database.', $address));
        }

        $timezone = @geoip_time_zone_by_country_and_region($results['country_code'], $results['region']) ?: null;
        $region   = @geoip_region_name_by_code($results['country_code'], $results['region']) ?: $results['region'];

        return $this->returnResults([
            array_merge($this->getDefaults(), [
                'latitude'    => $results['latitude'],
                'longitude'   => $results['longitude'],
                'locality'    => $results['city'],
                'postalCode'  => $results['postal_code'],
                'region'      => $region,
                'regionCode'  => $results['region'],
                'country'     => $results['country_name'],
                'countryCode' => $results['country_code'],
                'timezone'    => $timezone,
            ])
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        throw new UnsupportedOperation('The Geoip provider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'geoip';
    }
}
