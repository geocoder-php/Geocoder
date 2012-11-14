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
class HostIpProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://api.hostip.info/get_xml.php?ip=%s&position=true';

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The HostIpProvider does not support Street addresses.');
        }

        // This API does not support IPv6
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedException('The HostIpProvider does not support IPv6 addresses.');
        }

        if ('127.0.0.1' === $address) {
            return $this->getLocalhostDefaults();
        }

        $query = sprintf(self::ENDPOINT_URL, $address);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The HostIpProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'host_ip';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        try {
            $xml = new \SimpleXmlElement($content);
        } catch (\Exception $e) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $dataNode = $xml
            ->children('gml', true)
            ->featureMember
            ->children('', true)
            ->Hostip;

        if (isset($dataNode->ipLocation)) {
            $lngLat = explode(',', (string) $dataNode
                ->ipLocation
                ->children('gml', true)
                ->pointProperty
                ->Point
                ->coordinates
            );
        } else {
            $lngLat = array(null, null);
        }

        $city = (string) $dataNode
            ->children('gml', true)
            ->name;

        $country = (string) $dataNode
            ->countryName;

        $countryCode = (string) $dataNode
            ->countryAbbrev;

        return array_merge($this->getDefaults(), array(
            'latitude'    => $lngLat[1],
            'longitude'   => $lngLat[0],
            'city'        => $city,
            'country'     => $country,
            'countryCode' => $countryCode,
        ));
    }
}
