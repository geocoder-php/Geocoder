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
    private $apiKey = null;

    /**
     * @param string $apiKey
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
            return array(
                'city'      => 'localhost',
                'region'    => 'localhost',
                'country'   => 'localhost'
            );
        }

        $query   = sprintf('http://api.ipinfodb.com/v3/ip-city/?key=%s&format=json&ip=%s', $this->apiKey, $address);

        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            return $this->getDefaults();
        }

        $data = (array)json_decode($content);

        return array(
            'latitude'  => isset($data['latitude']) ? $data['latitude'] : null,
            'longitude' => isset($data['longitude']) ? $data['longitude'] : null,
            'city'      => isset($data['cityName']) ? $data['cityName'] : null,
            'zipcode'   => isset($data['zipCode']) ? $data['zipCode'] : null,
            'region'    => isset($data['regionName']) ? $data['regionName'] : null,
            'country'   => isset($data['countryName']) ? $data['countryName'] : null
        );
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
}
