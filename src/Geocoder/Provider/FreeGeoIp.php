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
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Collection;
use Geocoder\Model\Address;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
final class FreeGeoIp extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://freegeoip.net/json/%s';

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The FreeGeoIp provider does not support street addresses.');
        }

        if (in_array($address, array('127.0.0.1', '::1'))) {
            return $this->returnResults([ $this->getLocalhostDefaults() ]);
        }

        $query = sprintf(self::ENDPOINT_URL, $address);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        throw new UnsupportedOperation('The FreeGeoIp provider is not able to do reverse geocoding.');
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
     *
     * @return Collection
     */
    private function executeQuery($query)
    {
        $request = $this->getMessageFactory()->createRequest('GET', $query);
        $content = (string) $this->getHttpClient()->sendRequest($request)->getBody();

        if (empty($content)) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $data = (array) json_decode($content);

        if (empty($data)) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $adminLevels = [];

        if (! empty($data['region_name']) || ! empty($data['region_code'])) {
            $adminLevels[] = [
                'name' => isset($data['region_name']) ? $data['region_name'] : null,
                'code' => isset($data['region_code']) ? $data['region_code'] : null,
                'level' => 1
            ];
        }

        return $this->returnResults([
            array_merge($this->getDefaults(), array(
                'latitude'    => isset($data['latitude']) ? $data['latitude'] : null,
                'longitude'   => isset($data['longitude']) ? $data['longitude'] : null,
                'locality'    => isset($data['city']) ? $data['city'] : null,
                'postalCode'  => isset($data['zip_code']) ? $data['zip_code'] : null,
                'adminLevels' => $adminLevels,
                'country'     => isset($data['country_name']) ? $data['country_name'] : null,
                'countryCode' => isset($data['country_code']) ? $data['country_code'] : null,
                'timezone'    => isset($data['time_zone']) ? $data['time_zone'] : null,
            ))
        ]);
    }
}
