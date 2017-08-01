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
use Geocoder\Exception\InvalidCredentials;

/**
 * @author Saikiran Ch <contact@unwiredlabs.com>
 */
class LocationIQ extends AbstractHttpProvider implements LocaleAwareProvider
{
    use LocaleTrait;

    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://locationiq.org/v1/search.php?format=xml&addressdetails=1&key=%s&q=%s&limit=%d';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://locationiq.org/v1/reverse.php?format=xml&key=%s&lat=%f&lon=%f';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $apiKey  API key.
     * @param string               $locale  
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey = null)
    {
        parent::__construct($adapter);
        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'locationiq';
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided.');
        }

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('LocationIQ does not support IP addresses');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $this->apiKey, urlencode($address), $this->getLimit());
        $content = $this->executeQuery($query);

        if (empty($content)) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content)) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $searchResult = $doc->getElementsByTagName('searchresults')->item(0);
        if ($searchResult == null || $searchResult->getAttribute('error')) {
            $errorMessage = $searchResult->getAttribute('error');

            if (stripos($errorMessage, 'invalid key') !== FALSE) {
                throw new InvalidCredentials(sprintf('API Key is not valid %s', $query));
            } elseif (stripos($errorMessage, 'key not active') !== FALSE) {
                throw new NoResult(sprintf('API key is inactive %s', $query));
            } elseif (stripos($errorMessage, "Rate Limited") !== FALSE) {
                throw new NoResult(sprintf('API key is inactive %s', $query));
            } elseif (stripos($errorMessage, "unknown error") !== FALSE) {
                throw new Exception("Unknown error - Please try again after some time");
            }
            throw new NoResult(sprintf('Could not execute query %s', $query));
        }

        $places = $searchResult->getElementsByTagName('place');

        if (null === $places || 0 === $places->length) {
            throw new NoResult(sprintf('Could not execute query %s', $query));
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
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided.');
        }

        $query = sprintf(self::REVERSE_ENDPOINT_URL, $this->apiKey, $latitude, $longitude);
        $content = $this->executeQuery($query);

        if (empty($content)) {
            throw new NoResult(sprintf('Unable to find results for coordinates [ %f, %f ].', $latitude, $longitude));
        }

        $doc = new \DOMDocument();

        if (!@$doc->loadXML($content) || $doc->getElementsByTagName('error')->length > 0) {
            $errorMessage = $doc->getElementsByTagName('error')->item(0)->nodeValue;

            if (stripos($errorMessage, 'invalid key') !== FALSE) {
                throw new InvalidCredentials(sprintf('API Key is not valid %s', $query));
            } elseif (stripos($errorMessage, 'key not active') !== FALSE) {
                throw new NoResult(sprintf('API key is inactive %s', $query));
            } elseif (stripos($errorMessage, "Rate Limited") !== FALSE) {
                throw new NoResult(sprintf('API key is inactive %s', $query));
            } elseif (stripos($errorMessage, "unknown error") !== FALSE) {
                throw new Exception("Unknown error - Please try again after some time");
            }
            throw new NoResult(sprintf('Unable to find results for coordinates [ %f, %f ].', $latitude, $longitude));
        }

        $reverseGeocode = $doc->getElementsByTagName('reversegeocode')->item(0);
        $addressParts = $reverseGeocode->getElementsByTagName('addressparts')->item(0);
        $result       = $reverseGeocode->getElementsByTagName('result')->item(0);

        return $this->returnResults([
            array_merge($this->getDefaults(), $this->xmlResultToArray($result, $addressParts))
        ]);
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

    private function getNodeValue(\DOMNodeList $element)
    {
        return $element->length ? $element->item(0)->nodeValue : null;
    }
}