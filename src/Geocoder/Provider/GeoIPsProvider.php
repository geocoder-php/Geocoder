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
use Geocoder\Exception\InvalidArgumentException;
use Geocoder\Exception\QuotaExceededException;
use Geocoder\HttpAdapter\HttpAdapterInterface;

/**
 * @author Andrea Cristaudo <andrea.cristaudo@gmail.com>
 * @author Arthur Bodera <abodera@thinkscape.pro>
 *
 * @link http://www.geoips.com/en/developer/api-guide
 */
class GeoIPsProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://api.geoips.com/ip/%s/key/%s/output/json/timezone/true/';

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
    private $apiKey = null;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $apiKey  An API key.
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
            throw new UnsupportedException('The GeoIPsProvider does not support street addresses.');
        }

        // This API does not support IPv6
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedException('The GeoIPsProvider does not support IPv6 addresses.');
        }

        if ('127.0.0.1' === $address) {
            return array($this->getLocalhostDefaults());
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $address, $this->apiKey);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The GeoIPsProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'geo_ips';
    }

    /**
     * @param  string $query
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (null === $content || '' === $content) {
            throw new NoResultException(sprintf('Invalid response from GeoIPs server for query %s', $query));
        }

        $json = json_decode($content, true);

        if (isset($json['error'])) {
            switch ($json['error']['code']) {
                case static::CODE_BAD_IP:
                    throw new InvalidArgumentException('The API call should include a valid IP address.');
                case static::CODE_BAD_KEY:
                    throw new InvalidCredentialsException('The API call should include a API key parameter.');
                case static::CODE_NOT_AUTHORIZED:
                    throw new InvalidCredentialsException('The API key associated with your request was not recognized.');
                case static::CODE_ACCOUNT_INACTIVE:
                    throw new InvalidCredentialsException('The API key has not been approved or has been disabled.');
                case static::CODE_LIMIT_EXCEEDED:
                    throw new QuotaExceededException('The service you have requested is over capacity.');
                default:
                    throw new NoResultException(sprintf(
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
            throw new NoResultException(sprintf('Invalid response from GeoIPs server for query %s', $query));
        }

        $response = $json['response'];

        // Check response code
        switch ($response['code']) {
            case static::CODE_NOT_FOUND:
                throw new NoResultException();
            case static::CODE_SUCCESS;
                // everything is ok
                break;
            default:
                throw new NoResultException(sprintf(
                    'GeoIPs returned unknown result code %s for query: %s',
                    $response['code'],
                    $query
                ));
        }

        // Make sure that we do have proper result array
        if (empty($response['location']) || !is_array($response['location'])) {
            throw new NoResultException(sprintf('Invalid response from GeoIPs server for query %s', $query));
        }

        $locations = array();
        $location = $response['location'];
        $locations[] = array_merge($this->getDefaults(), array(
            'country'     => '' === $location['country_name'] ? null : $location['country_name'],
            'countryCode' => '' === $location['country_code'] ? null : $location['country_code'],
            'region'      => '' === $location['region_name']  ? null : $location['region_name'],
            'regionCode'  => '' === $location['region_code']  ? null : $location['region_code'],
            'county'      => '' === $location['county_name']  ? null : $location['county_name'],
            'city'        => '' === $location['city_name']    ? null : $location['city_name'],
            'latitude'    => '' === $location['latitude']     ? null : $location['latitude'],
            'longitude'   => '' === $location['longitude']    ? null : $location['longitude'],
            'timezone'    => '' === $location['timezone']     ? null : $location['timezone'],
        ));

        return $locations;
    }
}
