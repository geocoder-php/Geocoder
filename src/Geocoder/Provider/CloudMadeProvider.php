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
 * @author David Guyon <dguyon@gmail.com>
 */
class CloudMadeProvider extends AbstractProvider implements ProviderInterface
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
        parent::__construct($adapter, null);

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

        $query = sprintf('http://geocoding.cloudmade.com/%s/geocoding/v2/find.js?query=%s&distance=closest&return_location=true&results=1', $this->apiKey, urlencode($address));

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        if (null === $this->apiKey) {
            throw new \RuntimeException('No API Key provided');
        }

        $query = sprintf('http://geocoding.cloudmade.com/%s/geocoding/v2/find.js?around=%F,%F&object_type=address&return_location=true&results=1', $this->apiKey, $coordinates[0], $coordinates[1]);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'cloudmade';
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

        $json = json_decode($content);
        if (isset($json->found) && $json->found > 0) {
            $data = (array) $json->features[0];
        } else {
            return $this->getDefaults();
        }

        $coordinates = (array) $data['centroid']->coordinates;

        return array(
            'latitude'  => $coordinates[0],
            'longitude' => $coordinates[1],
            'city'      => isset($data['location']->city) ? $data['location']->city : null,
            'zipcode'   => isset($data['location']->zipcode) ? $data['location']->zipcode : null,
            'region'    => isset($data['location']->county) ? $data['location']->county : null,
            'country'   => isset($data['location']->country) ? $data['location']->country : null,
        );
    }
}
