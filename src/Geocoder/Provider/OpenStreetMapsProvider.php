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
 * @author Niklas Närhinen <niklas@narhinen.net>
 */
class OpenStreetMapsProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://nominatim.openstreetmap.org/search?q=%s&format=xml&addressdetails=1&limit=1';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://nominatim.openstreetmap.org/reverse?format=xml&lat=%F&lon=%F&addressdetails=1&zoom=18';

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        // This API does not support IPv6
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedException('The OpenStreetMapsProvider does not support IPv6 addresses.');
        }

        if ('127.0.0.1' === $address) {
            return $this->getLocalhostDefaults();
        }

        $query   = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address));
        $content = $this->executeQuery($query);

        if (null === $content) {
            throw new NoResultException(sprintf('Could not resolve address "%s"', $address));
        }

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content) || null === $doc->getElementsByTagName('searchresults')->item(0)) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $searchResult = $doc->getElementsByTagName('searchresults')->item(0);
        $place        = $searchResult->getElementsByTagName('place')->item(0);

        if (null === $place) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $boundsAttr = $place->getAttribute('boundingbox');
        list($bounds['south'], $bounds['north'], $bounds['west'], $bounds['east']) = $boundsAttr ? explode(',', $boundsAttr) : null;

        $ret = $this->getDefaults();

        $ret['latitude']     = $place->getAttribute('lat');
        $ret['longitude']    = $place->getAttribute('lon');
        $ret['bounds']       = $bounds;
        $ret['zipcode']      = $this->getNodeValue($place->getElementsByTagName('postcode'));
        $ret['county']       = $this->getNodeValue($place->getElementsByTagName('county'));
        $ret['region']       = $this->getNodeValue($place->getElementsByTagName('state'));
        $ret['streetNumber'] = $this->getNodeValue($place->getElementsByTagName('house_number'));
        $ret['streetName']   = $this->getNodeValue($place->getElementsByTagName('road'));
        $ret['city']         = $this->getNodeValue($place->getElementsByTagName('city'));
        $ret['cityDistrict'] = $this->getNodeValue($place->getElementsByTagName('suburb'));
        $ret['country']      = $this->getNodeValue($place->getElementsByTagName('country'));
        $ret['countryCode']  = strtoupper($this->getNodeValue($place->getElementsByTagName('country_code')));

        return array_merge($this->getDefaults(), $ret);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        $query   = sprintf(self::REVERSE_ENDPOINT_URL, $coordinates[0], $coordinates[1]);
        $content = $this->executeQuery($query);

        if (null === $content) {
            throw new NoResultException(sprintf('Unable to resolve the coordinates %s', implode(', ', $coordinates)));
        }

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content) || $doc->getElementsByTagName('error')->length > 0) {
            throw new NoResultException(sprintf('Could not resolve coordinates %s', implode(', ', $coordinates)));
        }

        $searchResult = $doc->getElementsByTagName('reversegeocode')->item(0);
        $addressParts = $searchResult->getElementsByTagName('addressparts')->item(0);
        $result       = $searchResult->getElementsByTagName('result')->item(0);
        $ret          = $this->getDefaults();

        $ret['latitude']     = $result->getAttribute('lat');
        $ret['longitude']    = $result->getAttribute('lon');
        $ret['zipcode']      = $this->getNodeValue($addressParts->getElementsByTagName('postcode'));
        $ret['county']       = $this->getNodeValue($addressParts->getElementsByTagName('county'));
        $ret['region']       = $this->getNodeValue($addressParts->getElementsByTagName('state'));
        $ret['streetNumber'] = $this->getNodeValue($addressParts->getElementsByTagName('house_number'));
        $ret['streetName']   = $this->getNodeValue($addressParts->getElementsByTagName('road'));
        $ret['city']         = $this->getNodeValue($addressParts->getElementsByTagName('city'));
        $ret['cityDistrict'] = $this->getNodeValue($addressParts->getElementsByTagName('suburb'));
        $ret['country']      = $this->getNodeValue($addressParts->getElementsByTagName('country'));
        $ret['countryCode']  = strtoupper($this->getNodeValue($addressParts->getElementsByTagName('country_code')));

        return array_merge($this->getDefaults(), $ret);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'openstreetmaps';
    }

    /**
     * @param string $query
     *
     * @return string
     */
    protected function executeQuery($query)
    {
        if (null !== $this->getLocale()) {
            $query = sprintf('%s&accept-language=%s', $query, $this->getLocale());
        }

        return $this->getAdapter()->getContent($query);
    }

    private function getNodeValue($element)
    {
        return $element->length ? $element->item(0)->nodeValue : null;
    }
}
