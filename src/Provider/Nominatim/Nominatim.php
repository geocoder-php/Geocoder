<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Nominatim;

use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Exception\ZeroResults;
use Geocoder\Model\Query\GeocodeQuery;
use Geocoder\Model\Query\ReverseQuery;
use Geocoder\Provider\AbstractHttpProvider;
use Geocoder\Provider\LocaleAwareGeocoder;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author Niklas Närhinen <niklas@narhinen.net>
 */
final class Nominatim extends AbstractHttpProvider implements LocaleAwareGeocoder, Provider
{
    /**
     * @var string
     */
    private $rootUrl;

    /**
     * @param HttpClient  $client
     * @param string|null $locale
     *
     * @return Nominatim
     */
    public static function withOpenStreetMapServer(HttpClient $client)
    {
        return new self($client, 'https://nominatim.openstreetmap.org');
    }

    /**
     * @param HttpClient $client  an HTTP adapter
     * @param string     $rootUrl Root URL of the nominatim server
     */
    public function __construct(HttpClient $client, $rootUrl)
    {
        parent::__construct($client);

        $this->rootUrl = rtrim($rootUrl, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query)
    {
        $address = $query->getText();
        // This API does not support IPv6
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedOperation('The Nominatim provider does not support IPv6 addresses.');
        }

        if ('127.0.0.1' === $address) {
            return $this->returnResults([$this->getLocalhostDefaults()]);
        }

        $url = sprintf($this->getGeocodeEndpointUrl(), urlencode($address), $query->getLimit());
        $content = $this->executeQuery($url, $query->getLocale());

        if (empty($content)) {
            throw InvalidServerResponse::create($url);
        }

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content) || null === $doc->getElementsByTagName('searchresults')->item(0)) {
            throw InvalidServerResponse::create($url);
        }

        $searchResult = $doc->getElementsByTagName('searchresults')->item(0);
        $places = $searchResult->getElementsByTagName('place');

        if (null === $places || 0 === $places->length) {
            throw ZeroResults::create($url);
        }

        $results = [];
        foreach ($places as $place) {
            $results[] = array_merge($this->getDefaults(), $this->xmlResultToArray($place, $place));
        }

        return $this->returnResults($results);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query)
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();
        $url = sprintf($this->getReverseEndpointUrl(), $latitude, $longitude);
        $content = $this->executeQuery($url, $query->getLocale());

        if (empty($content)) {
            throw new ZeroResults(sprintf('Unable to find results for coordinates [ %f, %f ].', $latitude, $longitude));
        }

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content) || $doc->getElementsByTagName('error')->length > 0) {
            throw new ZeroResults(sprintf('Unable to find results for coordinates [ %f, %f ].', $latitude, $longitude));
        }

        $searchResult = $doc->getElementsByTagName('reversegeocode')->item(0);
        $addressParts = $searchResult->getElementsByTagName('addressparts')->item(0);
        $result = $searchResult->getElementsByTagName('result')->item(0);

        return $this->returnResults([
            array_merge($this->getDefaults(), $this->xmlResultToArray($result, $addressParts)),
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
            'latitude' => $resultNode->getAttribute('lat'),
            'longitude' => $resultNode->getAttribute('lon'),
            'postalCode' => $postalCode,
            'adminLevels' => $adminLevels,
            'streetNumber' => $this->getNodeValue($addressNode->getElementsByTagName('house_number')),
            'streetName' => $this->getNodeValue($addressNode->getElementsByTagName('road')) ?: $this->getNodeValue($addressNode->getElementsByTagName('pedestrian')),
            'locality' => $this->getNodeValue($addressNode->getElementsByTagName('city')),
            'subLocality' => $this->getNodeValue($addressNode->getElementsByTagName('suburb')),
            'country' => $this->getNodeValue($addressNode->getElementsByTagName('country')),
            'countryCode' => strtoupper($this->getNodeValue($addressNode->getElementsByTagName('country_code'))),
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'nominatim';
    }

    /**
     * @param string $query
     * @param string $locale
     */
    private function executeQuery($query, $locale)
    {
        if (null !== $locale) {
            $query = sprintf('%s&accept-language=%s', $query, $locale);
        }

        $request = $this->getMessageFactory()->createRequest('GET', $query);

        return (string) $this->getHttpClient()->sendRequest($request)->getBody();
    }

    private function getGeocodeEndpointUrl()
    {
        return $this->rootUrl.'/search?q=%s&format=xml&addressdetails=1&limit=%d';
    }

    private function getReverseEndpointUrl()
    {
        return $this->rootUrl.'/reverse?format=xml&lat=%F&lon=%F&addressdetails=1&zoom=18';
    }

    private function getNodeValue(\DOMNodeList $element)
    {
        return $element->length ? $element->item(0)->nodeValue : null;
    }
}
