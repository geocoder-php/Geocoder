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
class MapQuestProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://open.mapquestapi.com/geocoding/v1/address?location=%s&outFormat=json&maxResults=1&thumbMaps=false';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://open.mapquestapi.com/geocoding/v1/reverse?lat=%F&lng=%F';

    /**
     * @param \Geocoder\HttpAdapter\HttpAdapterInterface $adapter
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
            return $this->getLocalhostDefaults();
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address));

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        $query = sprintf(self::REVERSE_ENDPOINT_URL, $coordinates[0], $coordinates[1]);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'map_quest';
    }

    /**
     * @param  string $query
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            return $this->getDefaults();
        }

        $json = json_decode($content, true);

        if (isset($json['results']) && !empty($json['results'])) {
            $result = current($json['results']);

            if (isset($result['locations']) && !empty($result['locations'])) {
                $location = current($result['locations']);

                // TODO: maybe add more information using the link below:
                // http://open.mapquestapi.com/geocoding/
                return array(
                    'latitude'      => $location['latLng']['lat'],
                    'longitude'     => $location['latLng']['lng'],
                    'bounds'        => null,
                    'streetNumber'  => null,
                    'streetName'    => $location['street'] ?: null,
                    'city'          => $location['adminArea5'] ?: null,
                    'zipcode'       => $location['postalCode'] ?: null,
                    'cityDistrict'  => null,
                    'county'        => $location['adminArea4'] ?: null,
                    'region'        => $location['adminArea3'] ?: null,
                    'regionCode'    => null,
                    'country'       => $location['adminArea1'] ?: null,
                    'countryCode'   => null,
                    'timezone'      => null,
                );
            }
        }

        return $this->getDefaults();
    }
}
