<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\NoResult;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Exception\UnsupportedOperation;
use Ivory\HttpAdapter\HttpAdapterInterface;

/**
 * @author Andrea Cristaudo <andrea.cristaudo@gmail.com>
 * @author Arthur Bodera <abodera@thinkscape.pro>
 *
 * @link http://www.geoips.com/en/developer/api-guide
 */
class GeoIPs extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL  = 'http://api.geoips.com/ip/%s/key/%s/output/json/timezone/true/';

    const CODE_SUCCESS          = '200_1'; // The following results has been returned.

    const CODE_NOT_FOUND        = '200_2'; // No result set has been returned.

    const CODE_BAD_KEY          = '400_1'; // Error in the URI - The API call should include a API key parameter.

    const CODE_BAD_IP           = '400_2'; // Error in the URI - The API call should include a valid IP address.

    const CODE_NOT_AUTHORIZED   = '403_1'; // The API key associated with your request was not recognized.

    const CODE_ACCOUNT_INACTIVE = '403_2'; // The API key has not been approved or has been disabled.

    const CODE_LIMIT_EXCEEDED   = '403_3'; // The service you have requested is over capacity.

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter
     * @param string               $apiKey  An API key
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey)
    {
        parent::__construct($adapter);

        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API key provided.');
        }

        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The GeoIPs provider does not support street addresses, only IPv4 addresses.');
        }

        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedOperation('The GeoIPs provider does not support IPv6 addresses, only IPv4 addresses.');
        }

        if ('127.0.0.1' === $address) {
            return $this->returnResults([ $this->getLocalhostDefaults() ]);
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $address, $this->apiKey);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        throw new UnsupportedOperation('The GeoIPs provider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'geo_ips';
    }

    /**
     * @param string $query
     */
    private function executeQuery($query)
    {
        $content = (string) $this->getAdapter()->get($query)->getBody();

        if (empty($content)) {
            throw new NoResult(sprintf('Invalid response from GeoIPs API for query "%s".', $query));
        }

        $json = json_decode($content, true);

        if (isset($json['error'])) {
            switch ($json['error']['code']) {
                case static::CODE_BAD_IP:
                    throw new InvalidArgument('The API call should include a valid IP address.');
                case static::CODE_BAD_KEY:
                    throw new InvalidCredentials('The API call should include a API key parameter.');
                case static::CODE_NOT_AUTHORIZED:
                    throw new InvalidCredentials('The API key associated with your request was not recognized.');
                case static::CODE_ACCOUNT_INACTIVE:
                    throw new InvalidCredentials('The API key has not been approved or has been disabled.');
                case static::CODE_LIMIT_EXCEEDED:
                    throw new QuotaExceeded('The service you have requested is over capacity.');
                default:
                    throw new NoResult(sprintf(
                        'GeoIPs error %s%s%s%s - query: %s',
                        $json['error']['code'],
                        isset($json['error']['status']) ? ', ' . $json['error']['status'] : '',
                        isset($json['error']['message']) ? ', ' . $json['error']['message'] : '',
                        isset($json['error']['notes']) ? ', ' . $json['error']['notes'] : '',
                        $query
                    ));
            }
        }

        if (!is_array($json) || empty($json) || empty($json['response']) || empty($json['response']['code'])) {
            throw new NoResult(sprintf('Invalid response from GeoIPs API for query "%s".', $query));
        }

        $response = $json['response'];

        // Check response code
        switch ($response['code']) {
            case static::CODE_NOT_FOUND:
                throw new NoResult();
            case static::CODE_SUCCESS;
                // everything is ok
                break;
            default:
                throw new NoResult(sprintf(
                    'The GeoIPs API returned unknown result code "%s" for query: "%s".',
                    $response['code'],
                    $query
                ));
        }

        // Make sure that we do have proper result array
        if (empty($response['location']) || !is_array($response['location'])) {
            throw new NoResult(sprintf('Invalid response from GeoIPs API for query "%s".', $query));
        }

        $location = array_map(function ($value) {
            return '' === $value ? null : $value;
        }, $response['location']);

        $adminLevels = [];

        if (null !== $location['region_name'] || null !== $location['region_code']) {
            $adminLevels[] = [
                'name' => $location['region_name'],
                'code' => $location['region_code'],
                'level' => 1
            ];
        }

        if (null !== $location['county_name']) {
            $adminLevels[] = [
                'name' => $location['county_name'],
                'level' => 2
            ];
        }

        $results   = [];
        $results[] = array_merge($this->getDefaults(), array(
            'country'     => $location['country_name'],
            'countryCode' => $location['country_code'],
            'adminLevels' => $adminLevels,
            'locality'    => $location['city_name'],
            'latitude'    => $location['latitude'],
            'longitude'   => $location['longitude'],
            'timezone'    => $location['timezone'],
        ));

        return $this->returnResults($results);
    }
}
