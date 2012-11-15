<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\NoResultException;
use Geocoder\Exception\UnsupportedException;

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
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The MapQuestProvider does not support IP addresses.');
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
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $json = json_decode($content, true);

        if (isset($json['results']) && !empty($json['results'])) {
            $result = current($json['results']);

            if (isset($result['locations']) && !empty($result['locations'])) {
                $location = current($result['locations']);

                // TODO: maybe add more information using the link below:
                // http://open.mapquestapi.com/geocoding/
                return array_merge($this->getDefaults(), array(
                    'latitude'      => $location['latLng']['lat'],
                    'longitude'     => $location['latLng']['lng'],
                    'streetName'    => $location['street'] ?: null,
                    'city'          => $location['adminArea5'] ?: null,
                    'zipcode'       => $location['postalCode'] ?: null,
                    'county'        => $location['adminArea4'] ?: null,
                    'region'        => $location['adminArea3'] ?: null,
                    'country'       => $location['adminArea1'] ?: null,
                ));
            }
        }

        throw new NoResultException(sprintf('Could not find results for given query: %s', $query));
    }
}
