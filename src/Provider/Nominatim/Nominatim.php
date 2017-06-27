<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Nominatim;

use Geocoder\Collection;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Location;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author Niklas Närhinen <niklas@narhinen.net>
 */
final class Nominatim extends AbstractHttpProvider implements Provider
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
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();
        // This API does not support IPv6
        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new UnsupportedOperation('The Nominatim provider does not support IPv6 addresses.');
        }

        if ('127.0.0.1' === $address) {
            return new AddressCollection([$this->getLocationForLocalhost()]);
        }

        $url = sprintf($this->getGeocodeEndpointUrl(), urlencode($address), $query->getLimit());
        $content = $this->executeQuery($url, $query->getLocale());

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content) || null === $doc->getElementsByTagName('searchresults')->item(0)) {
            throw InvalidServerResponse::create($url);
        }

        $searchResult = $doc->getElementsByTagName('searchresults')->item(0);
        $places = $searchResult->getElementsByTagName('place');

        if (null === $places || 0 === $places->length) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($places as $place) {
            $results[] = $this->xmlResultToArray($place, $place);
        }

        return new AddressCollection($results);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();
        $url = sprintf($this->getReverseEndpointUrl(), $latitude, $longitude, $query->getData('zoom', 18));
        $content = $this->executeQuery($url, $query->getLocale());

        $doc = new \DOMDocument();
        if (!@$doc->loadXML($content) || $doc->getElementsByTagName('error')->length > 0) {
            return new AddressCollection([]);
        }

        $searchResult = $doc->getElementsByTagName('reversegeocode')->item(0);
        $addressParts = $searchResult->getElementsByTagName('addressparts')->item(0);
        $result = $searchResult->getElementsByTagName('result')->item(0);

        return new AddressCollection([$this->xmlResultToArray($result, $addressParts)]);
    }

    /**
     * @param \DOMElement $resultNode
     * @param \DOMElement $addressNode
     *
     * @return Location
     */
    private function xmlResultToArray(\DOMElement $resultNode, \DOMElement $addressNode): Location
    {
        $builder = new AddressBuilder($this->getName());

        foreach (['state', 'county'] as $i => $tagName) {
            if (null !== ($adminLevel = $this->getNodeValue($addressNode->getElementsByTagName($tagName)))) {
                $builder->addAdminLevel($i + 1, $adminLevel, '');
            }
        }

        // get the first postal-code when there are many
        $postalCode = $this->getNodeValue($addressNode->getElementsByTagName('postcode'));
        if (!empty($postalCode)) {
            $postalCode = current(explode(';', $postalCode));
        }
        $builder->setPostalCode($postalCode);
        $builder->setStreetName($this->getNodeValue($addressNode->getElementsByTagName('road')) ?: $this->getNodeValue($addressNode->getElementsByTagName('pedestrian')));
        $builder->setStreetNumber($this->getNodeValue($addressNode->getElementsByTagName('house_number')));
        $builder->setLocality($this->getNodeValue($addressNode->getElementsByTagName('city')));
        $builder->setSubLocality($this->getNodeValue($addressNode->getElementsByTagName('suburb')));
        $builder->setCountry($this->getNodeValue($addressNode->getElementsByTagName('country')));
        $builder->setCountryCode(strtoupper($this->getNodeValue($addressNode->getElementsByTagName('country_code'))));
        $builder->setCoordinates($resultNode->getAttribute('lat'), $resultNode->getAttribute('lon'));

        $boundsAttr = $resultNode->getAttribute('boundingbox');
        if ($boundsAttr) {
            $bounds = [];
            list($bounds['south'], $bounds['north'], $bounds['west'], $bounds['east']) = explode(',', $boundsAttr);
            $builder->setBounds($bounds['south'], $bounds['north'], $bounds['west'], $bounds['east']);
        }

        return $builder->build();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'nominatim';
    }

    /**
     * @param string      $url
     * @param string|null $locale
     *
     * @return string
     */
    private function executeQuery(string $url, string $locale = null): string
    {
        if (null !== $locale) {
            $url = sprintf('%s&accept-language=%s', $url, $locale);
        }

        return $this->getUrlContents($url);
    }

    private function getGeocodeEndpointUrl(): string
    {
        return $this->rootUrl.'/search?q=%s&format=xml&addressdetails=1&limit=%d';
    }

    private function getReverseEndpointUrl(): string
    {
        return $this->rootUrl.'/reverse?format=xml&lat=%F&lon=%F&addressdetails=1&zoom=%d';
    }

    private function getNodeValue(\DOMNodeList $element)
    {
        return $element->length ? $element->item(0)->nodeValue : null;
    }
}
