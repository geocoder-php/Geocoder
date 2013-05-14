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
    const GEOCODE_ENDPOINT_URL = 'http://nominatim.openstreetmap.org/search?q=%s&format=xml&addressdetails=1&limit=%d';

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
            return array($this->getLocalhostDefaults());
        }

        $query   = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address), $this->getMaxResults());
        $content = $this->executeQuery($query);

        if (null === $content) {
            throw new NoResultException(sprintf('Could not resolve address "%s"', $address));
        }

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content) || null === $doc->getElementsByTagName('searchresults')->item(0)) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $searchResult = $doc->getElementsByTagName('searchresults')->item(0);
        $places = $searchResult->getElementsByTagName('place');

        if (null === $places || 0 === $places->length) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $results = array();

        foreach ($places as $place) {
            $boundsAttr = $place->getAttribute('boundingbox');
            list($bounds['south'], $bounds['north'], $bounds['west'], $bounds['east']) = $boundsAttr
                ? explode(',', $boundsAttr)
                : null;

            $results[] = array_merge($this->getDefaults(), array(
                'latitude'     => $place->getAttribute('lat'),
                'longitude'    => $place->getAttribute('lon'),
                'bounds'       => $bounds,
                'zipcode'      => $this->getNodeValue($place->getElementsByTagName('postcode')),
                'county'       => $this->getNodeValue($place->getElementsByTagName('county')),
                'region'       => $this->getNodeValue($place->getElementsByTagName('state')),
                'streetNumber' => $this->getNodeValue($place->getElementsByTagName('house_number')),
                'streetName'   => $this->getNodeValue($place->getElementsByTagName('road')),
                'city'         => $this->getNodeValue($place->getElementsByTagName('city')),
                'cityDistrict' => $this->getNodeValue($place->getElementsByTagName('suburb')),
                'country'      => $this->getNodeValue($place->getElementsByTagName('country')),
                'countryCode'  => strtoupper($this->getNodeValue($place->getElementsByTagName('country_code'))),
            ));
        }

        return $results;
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

        return array(array_merge($this->getDefaults(), array(
            'latitude'     => $result->getAttribute('lat'),
            'longitude'    => $result->getAttribute('lon'),
            'zipcode'      => $this->getNodeValue($addressParts->getElementsByTagName('postcode')),
            'county'       => $this->getNodeValue($addressParts->getElementsByTagName('county')),
            'region'       => $this->getNodeValue($addressParts->getElementsByTagName('state')),
            'streetNumber' => $this->getNodeValue($addressParts->getElementsByTagName('house_number')),
            'streetName'   => $this->getNodeValue($addressParts->getElementsByTagName('road')),
            'city'         => $this->getNodeValue($addressParts->getElementsByTagName('city')),
            'cityDistrict' => $this->getNodeValue($addressParts->getElementsByTagName('suburb')),
            'country'      => $this->getNodeValue($addressParts->getElementsByTagName('country')),
            'countryCode'  => strtoupper($this->getNodeValue($addressParts->getElementsByTagName('country_code'))),
        )));
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

    /**
     * @param \DOMNodeList
     *
     * @return string
     */
    private function getNodeValue(\DOMNodeList $element)
    {
        return $element->length ? $element->item(0)->nodeValue : null;
    }
}
