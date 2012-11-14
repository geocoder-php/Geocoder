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
class GeocoderCaProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://geocoder.ca/?geoit=xml&locate=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://geocoder.ca/?geoit=xml&reverse=1&latt=%F&longt=%F';

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The GeocoderCaProvider does not support IP addresses.');
        }

        $query   = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address));
        $content = $this->getAdapter()->getContent($query);

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content) || $doc->getElementsByTagName('error')->length) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        return array_merge($this->getDefaults(), array(
            'latitude'  => $this->getNodeValue($doc->getElementsByTagName('latt')),
            'longitude' => $this->getNodeValue($doc->getElementsByTagName('longt'))
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        $query   = sprintf(self::REVERSE_ENDPOINT_URL, $coordinates[0], $coordinates[1]);
        $content = $this->getAdapter()->getContent($query);

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content) || $doc->getElementsByTagName('error')->length) {
            throw new NoResultException(sprintf('Could not resolve coordinates %s', implode(', ', $coordinates)));
        }

        return array_merge($this->getDefaults(), array(
            'latitude'     => $this->getNodeValue($doc->getElementsByTagName('latt')),
            'longitude'    => $this->getNodeValue($doc->getElementsByTagName('longt')),
            'streetNumber' => $this->getNodeValue($doc->getElementsByTagName('stnumber')),
            'streetName'   => $this->getNodeValue($doc->getElementsByTagName('staddress')),
            'city'         => $this->getNodeValue($doc->getElementsByTagName('city')),
            'zipcode'      => $this->getNodeValue($doc->getElementsByTagName('postal')),
            'cityDistrict' => $this->getNodeValue($doc->getElementsByTagName('prov')),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'geocoder_ca';
    }

    private function getNodeValue($element)
    {
        return $element->length ? $element->item(0)->nodeValue : null;
    }
}
