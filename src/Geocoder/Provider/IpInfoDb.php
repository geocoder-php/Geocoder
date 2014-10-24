<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Exception\NoResult;
use Geocoder\Exception\InvalidCredentials;
use Geocoder\HttpAdapter\HttpAdapterInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class IpInfoDb extends AbstractProvider implements Provider
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://api.ipinfodb.com/v3/ip-city/?key=%s&format=json&ip=%s';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $apiKey  An API key.
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey)
    {
        parent::__construct($adapter);

        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided');
        }

        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The IpInfoDbProvider does not support Street addresses.');
        }

        // This API does not support IPv6
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedOperation('The IpInfoDbProvider does not support IPv6 addresses.');
        }

        if ('127.0.0.1' === $address) {
            return array($this->getLocalhostDefaults());
        }

        $query = sprintf(self::ENDPOINT_URL, $this->apiKey, $address);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedOperation('The IpInfoDbProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'ip_info_db';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    private function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $data = (array) json_decode($content);

        if (empty($data) || 'OK' !== $data['statusCode']) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $timezone = null;
        if (isset($data['timeZone'])) {
            $timezone = timezone_name_from_abbr("", (int) substr($data['timeZone'], 0, strpos($data['timeZone'], ':')) * 3600, 0);
        }

        return array(array_merge($this->getDefaults(), array(
            'latitude'    => isset($data['latitude']) ? $data['latitude'] : null,
            'longitude'   => isset($data['longitude']) ? $data['longitude'] : null,
            'locality'    => isset($data['cityName']) ? $data['cityName'] : null,
            'postalCode'  => isset($data['zipCode']) ? $data['zipCode'] : null,
            'region'      => isset($data['regionName']) ? $data['regionName'] : null,
            'country'     => isset($data['countryName']) ? $data['countryName'] : null,
            'countryCode' => isset($data['countryName']) ? $data['countryCode'] : null,
            'timezone'    => $timezone,
        )));
    }
}
