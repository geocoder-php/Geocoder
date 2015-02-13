<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\NoResult;
use Geocoder\Exception\UnsupportedOperation;
use Ivory\HttpAdapter\HttpAdapterInterface;

/**
 * @author Niklas Närhinen <niklas@narhinen.net>
 */
class Nominatim extends AbstractHttpProvider implements LocaleAwareProvider
{
    use LocaleTrait;

    /**
     * @var string
     */
    private $rootUrl;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $rootUrl Root URL of the nominatim server
     * @param string               $locale  A locale (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $rootUrl, $locale = null)
    {
        parent::__construct($adapter);

        $this->rootUrl = rtrim($rootUrl, '/');
        $this->locale  = $locale;
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        // This API does not support IPv6
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedOperation('The ' . get_called_class() . ' provider does not support IPv6 addresses.');
        }

        if ('127.0.0.1' === $address) {
            return $this->returnResults([ $this->getLocalhostDefaults() ]);
        }

        $query   = sprintf($this->getGeocodeEndpointUrl(), urlencode($address), $this->getLimit());
        $content = $this->executeQuery($query);

        if (empty($content)) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content) || null === $doc->getElementsByTagName('searchresults')->item(0)) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        $searchResult = $doc->getElementsByTagName('searchresults')->item(0);
        $places = $searchResult->getElementsByTagName('place');

        if (null === $places || 0 === $places->length) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        $results = [];
        foreach ($places as $place) {
            $results[] = array_merge($this->getDefaults(), $this->xmlResultToArray($place, $place));
        }

        return $this->returnResults($results);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        $query   = sprintf($this->getReverseEndpointUrl(), $latitude, $longitude);
        $content = $this->executeQuery($query);

        if (empty($content)) {
            throw new NoResult(sprintf('Unable to find results for coordinates [ %f, %f ].', $latitude, $longitude));
        }

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content) || $doc->getElementsByTagName('error')->length > 0) {
            throw new NoResult(sprintf('Unable to find results for coordinates [ %f, %f ].', $latitude, $longitude));
        }

        $searchResult = $doc->getElementsByTagName('reversegeocode')->item(0);
        $addressParts = $searchResult->getElementsByTagName('addressparts')->item(0);
        $result       = $searchResult->getElementsByTagName('result')->item(0);

        return $this->returnResults([
            array_merge($this->getDefaults(), $this->xmlResultToArray($result, $addressParts))
        ]);
    }

    private function xmlResultToArray(\DOMElement $resultNode, \DOMElement $addressNode)
    {
        $adminLevels = [];

        foreach (['state', 'county'] as $i => $tagName) {
            if (null !== ($adminLevel = $this->getNodeValue($addressNode->getElementsByTagName($tagName)))) {
                $adminLevels[] = ['name' => $adminLevel, 'level' => $i + 1];
            }
        }

        // get the first postal-code when there are many
        $postalCode = current(explode(';',
            $this->getNodeValue($addressNode->getElementsByTagName('postcode'))
        ));

        $result = [
            'latitude'     => $resultNode->getAttribute('lat'),
            'longitude'    => $resultNode->getAttribute('lon'),
            'postalCode'   => $postalCode,
            'adminLevels'  => $adminLevels,
            'streetNumber' => $this->getNodeValue($addressNode->getElementsByTagName('house_number')),
            'streetName'   => $this->getNodeValue($addressNode->getElementsByTagName('road')) ?: $this->getNodeValue($addressNode->getElementsByTagName('pedestrian')),
            'locality'     => $this->getNodeValue($addressNode->getElementsByTagName('city')),
            'subLocality'  => $this->getNodeValue($addressNode->getElementsByTagName('suburb')),
            'country'      => $this->getNodeValue($addressNode->getElementsByTagName('country')),
            'countryCode'  => strtoupper($this->getNodeValue($addressNode->getElementsByTagName('country_code'))),
        ];

        $boundsAttr = $resultNode->getAttribute('boundingbox');
        if ($boundsAttr) {
            $bounds = [];

            list($bounds['south'], $bounds['north'], $bounds['west'], $bounds['east']) = explode(',', $boundsAttr);

            $result['bounds'] = $bounds;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'nominatim';
    }

    /**
     * @param string $query
     */
    private function executeQuery($query)
    {
        if (null !== $this->getLocale()) {
            $query = sprintf('%s&accept-language=%s', $query, $this->getLocale());
        }

        return (string) $this->getAdapter()->get($query)->getBody();
    }

    private function getGeocodeEndpointUrl()
    {
        return $this->rootUrl . '/search?q=%s&format=xml&addressdetails=1&limit=%d';
    }

    private function getReverseEndpointUrl()
    {
        return $this->rootUrl . '/reverse?format=xml&lat=%F&lon=%F&addressdetails=1&zoom=18';
    }

    private function getNodeValue(\DOMNodeList $element)
    {
        return $element->length ? $element->item(0)->nodeValue : null;
    }
}
