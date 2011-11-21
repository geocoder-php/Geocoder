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
class GoogleMapsProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @param string $apiKey
     */
    public function __construct(HttpAdapterInterface $adapter)
    {
        parent::__construct($adapter, null);
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if ('127.0.0.1' === $address) {
            return array(
                'city'      => 'localhost',
                'region'    => 'localhost',
                'country'   => 'localhost'
            );
        }

        $query = sprintf('http://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false', urlencode($address));

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        return $this->getGeocodedData(sprintf('%s,%s', $coordinates[0], $coordinates[1]));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'google_maps';
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

        // API error
        if (!isset($json) || 'OK' !== $json->status)
        {
            return $this->getDefaults();
        }

        // no result
        if (!isset($json->results) || !count($json->results))
        {
            return $this->getDefaults();
        }

        $result = $json->results[0];
        $resultset = $this->getDefaults();

        // update address components
        foreach ($result->address_components as $component)
        {
          foreach ($component->types as $type)
          {
            $this->updateAddressComponent($resultset, $type, $component->long_name);
          }
        }

        // update coordinates
        $coordinates = $result->geometry->location;
        $resultset['latitude']  = $coordinates->lat;
        $resultset['longitude'] = $coordinates->lng;

        return $resultset;
    }

    /**
     * Update current resultset with given key/value.
     *
     * @param array   $resultset  resultset to update.
     * @param String  $type       component type.
     * @param String  $value      the component value;
     * @return array
     */
    protected function updateAddressComponent(&$resultset, $type, $value)
    {

        switch ($type)
        {
            case 'postal_code':
                $resultset['zipcode'] = $value;
                break;

            case 'locality':
                $resultset['city'] = $value;
                break;

            case 'administrative_area_level_1':
                $resultset['region'] = $value;
                break;

            case 'country':
                $resultset['country'] = $value;
                break;

            default:
                break;
        }
        return $resultset;
    }
}
