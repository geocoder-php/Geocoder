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
class BingMapsProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @param string $apiKey
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey, $locale = null)
    {
        parent::__construct($adapter, $locale);

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

        $query = sprintf('http://dev.virtualearth.net/REST/v1/Locations/?q=%s&key=%s', urlencode($address), $this->apiKey);

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

        $query = sprintf('http://dev.virtualearth.net/REST/v1/Locations/%s,%s?key=%s', $coordinates[0], $coordinates[1], $this->apiKey);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'bing_maps';
    }

    /**
     * @param string $query
     * @return array
     */
    protected function executeQuery($query)
    {
        if (null !== $this->getLocale()) {
            $query = sprintf('%s&culture=%s', $query, $this->getLocale());
        }

        $content = $this->getAdapter()->getContent($query);
        $data = (array)json_decode($content)->resourceSets[0]->resources[0];

        $coordinates = (array) $data['geocodePoints']->coordinates;

        $zipcode = (string) $data['address']->postalCode;
        $city = (string) $data['address']->locality;
        $region = (string) $data['address']->adminDistrict;
        $country = (string) $data['address']->countryRegion;

        return array(
            'latitude'  => $coordinates[0],
            'longitude' => $coordinates[1],
            'city'      => empty($city) ? null : $city,
            'zipcode'   => empty($zipcode) ? null : $zipcode,
            'region'    => empty($region) ? null : $region,
            'country'   => empty($country) ? null : $country
        );
    }
}
