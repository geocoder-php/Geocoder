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
    public function getData($value)
    {
        if (null === $this->apiKey) {
            throw new \RuntimeException('No API Key provided');
        }

        if ('127.0.0.1' === $value) {
            return array(
                'city'      => 'localhost',
                'region'    => 'localhost',
                'country'   => 'localhost'
            );
        }

        $query   = sprintf('http://api.ipinfodb.com/v3/ip-city/?key=%s&format=json&ip=%s', $this->apiKey, $value);
        $content = $this->getAdapter()->getContent($query);

        $data = (array)json_decode($content);

        return array(
            'latitude'  => $data['latitude'],
            'longitude' => $data['longitude'],
            'city'      => $data['cityName'],
            'zipcode'   => $data['zipCode'],
            'region'    => $data['regionName'],
            'country'   => $data['countryName']
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'ip_info_db';
    }
}
