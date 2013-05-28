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
 * @author Nicolas Chaulet <nchaulet.fr@gmail.com>
 */
class DataScienceToolkitProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const ENDPOINT_IP_URL = 'http://www.datasciencetoolkit.org/ip2coordinates/%s';

    /**
     * @var string
     */
    const ENDPOINT_ADRESS_URL = 'http://www.datasciencetoolkit.org/street2coordinates/%s';

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (empty($address)) {
            throw new UnsupportedException('The DataScienceToolkitProvider does not support empty addresses.');
        }

        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedException('The DataScienceToolkitProvider does not support IPv6 addresses.');
        }

        $endpoint = filter_var($address, FILTER_VALIDATE_IP) ? self::ENDPOINT_IP_URL : self::ENDPOINT_ADRESS_URL;

        if ('127.0.0.1' === $address) {
            return array($this->getLocalhostDefaults());
        }

        $query = sprintf($endpoint, urlencode($address));

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The DataScienceToolkitProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'data_science_toolkit';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);
        $result  = json_decode($content, true);

        if (!$result) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $result = array_shift($result);

        return array(array_merge($this->getDefaults(), array(
            'latitude'     => isset($result['latitude']) ? $result['latitude'] : null,
            'longitude'    => isset($result['longitude']) ? $result['longitude'] : null,
            'city'         => isset($result['locality']) ? $result['locality'] : null,
            'country'      => isset($result['country_name']) ? $result['country_name'] : null,
            'countryCode'  => isset($result['country_code']) ? $result['country_code'] : null,
            'zipcode'	   => isset($result['postal_code']) ? $result['postal_code'] : null,
            'streetName'   => isset($result['street_name']) ? $result['street_name'] : null,
            'streetNumber' => isset($result['street_number']) ? $result['street_number'] : null,
        )));
    }
}
