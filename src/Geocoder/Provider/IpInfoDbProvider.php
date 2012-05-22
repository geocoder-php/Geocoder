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
use Geocoder\HttpAdapter\HttpAdapterInterface;
use Geocoder\Provider\ProviderInterface;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class IpInfoDbProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://api.ipinfodb.com/v3/ip-city/?key=%s&format=json&ip=%s';

    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @param \Geocoder\HttpAdapter\HttpAdapterInterface $adapter
     * @param string                                     $apiKey
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
            throw new \RuntimeException('No API Key provided');
        }

        if ('127.0.0.1' === $address) {
            return $this->getLocalhostDefaults();
        }

        $query = sprintf(self::ENDPOINT_URL, $this->apiKey, $address);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The IpInfoDbProvider is not able to do reverse geocoding.');
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
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            return $this->getDefaults();
        }

        $data = (array)json_decode($content);

        if (empty($data) || 'OK' !== $data['statusCode']) {
            return $this->getDefaults();
        }

        return array(
            'latitude'    => isset($data['latitude']) ? $data['latitude'] : null,
            'longitude'   => isset($data['longitude']) ? $data['longitude'] : null,
            'city'        => isset($data['cityName']) ? $data['cityName'] : null,
            'zipcode'     => isset($data['zipCode']) ? $data['zipCode'] : null,
            'region'      => isset($data['regionName']) ? $data['regionName'] : null,
            'regionCode'  => null,
            'country'     => isset($data['countryName']) ? $data['countryName'] : null,
            'countryCode' => isset($data['countryName']) ? $data['countryCode'] : null
        );
    }
}
