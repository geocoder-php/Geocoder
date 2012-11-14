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
use Geocoder\Exception\NoResultException;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class GeocoderUsProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://geocoder.us/service/rest/?address=%s';

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The GeocoderUsProvider does not support IP addresses.');
        }

        $query = sprintf(self::ENDPOINT_URL, urlencode($address));

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The GeocoderUsProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'geocoder_us';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content)) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $xpath = new \SimpleXMLElement($content);
        $xpath->registerXPathNamespace('geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
        $lat  = $xpath->xpath('//geo:lat');
        $long = $xpath->xpath('//geo:long');

        return array_merge($this->getDefaults(), array(
            'latitude'  => isset($lat[0]) ? (double) $lat[0] : null,
            'longitude' => isset($long[0]) ? (double) $long[0] : null,
        ));
    }
}
