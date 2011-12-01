<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\HttpAdapter\HttpAdapterInterface;
use Geocoder\Provider\ProviderInterface;
use DOMDocument;

/**
 * @author Niklas Närhinen <niklas@narhinen.net>
 */
class OpenStreetMapsProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://nominatim.openstreetmap.org/search?q=%s&format=xml&addressdetails=1';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://nominatim.openstreetmap.org/reverse?format=xml&lat=%F&lon=%F&addressdetails=1';

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if ('127.0.0.1' === $address) {
            return $this->getLocalhostDefaults();
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address));

        $content = $this->executeQuery($query);

        if (null === $content) {
            return $this->getDefaults();
        }

        $doc = new DOMDocument();
        if (@$doc->loadXML($content)) {
            $maxRank      = 0;
            $bestNode     = null;
            $searchResult = $doc->getElementsByTagName('searchresults')->item(0);

            foreach ($searchResult->getElementsByTagName('place') as $node) {
                if ($node->getAttribute('place_rank') > $maxRank) {
                    $maxRank  = $node->getAttribute('place_rank');
                    $bestNode = $node;
                }
            }

            $ret = $this->getDefaults();
            $ret['latitude']  = $bestNode->getAttribute('lat');
            $ret['longitude'] = $bestNode->getAttribute('lon');
            $ret['zipcode']   = $bestNode->getElementsByTagName('postcode')->item(0)->nodeValue;
            $ret['county']    = $bestNode->getElementsByTagName('county')->length ? $bestNode->getElementsByTagName('county')->item(0)->nodeValue : null;
            $ret['region']    = $bestNode->getElementsByTagName('state')->length ? $bestNode->getElementsByTagName('state')->item(0)->nodeValue : null;
            $ret['streetNumber'] = $bestNode->getElementsByTagName('house_number')->item(0)->nodeValue;
            $ret['streetName']   = $bestNode->getElementsByTagName('road')->item(0)->nodeValue;
            $ret['city']      = $bestNode->getElementsByTagName('city')->item(0)->nodeValue;
            $ret['country']   = $bestNode->getElementsByTagName('country')->item(0)->nodeValue;

            return $ret;
        } else {
            return $this->getDefaults();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        $query = sprintf(self::REVERSE_ENDPOINT_URL, $coordinates[0], $coordinates[1]);

        $content = $this->executeQuery($query);

        if (null === $content) {
            return $this->getDefaults();
        }

        $doc = new DOMDocument();
        if (@$doc->loadXML($content)) {
            $maxRank          = 0;
            $bestNode         = null;
            $searchResult     = $doc->getElementsByTagName('reversegeocode')->item(0);
            $addressParts     = $searchResult->getElementsByTagName('addressparts')->item(0);
            $result           = $searchResult->getElementsByTagName('result')->item(0);
            $ret              = $this->getDefaults();
            $ret['latitude']  = $result->getAttribute('lat');
            $ret['longitude'] = $result->getAttribute('lon');
            $ret['zipcode']   = $addressParts->getElementsByTagName('postcode')->item(0)->nodeValue;
            $ret['county']    = $addressParts->getElementsByTagName('county')->length ? $addressParts->getElementsByTagName('county')->item(0)->nodeValue : null;
            $ret['region']    = $addressParts->getElementsByTagName('state')->length ? $addressParts->getElementsByTagName('state')->item(0)->nodeValue : null;
            $ret['streetNumber'] = $addressParts->getElementsByTagName('house_number')->item(0)->nodeValue;
            $ret['streetName']   = $addressParts->getElementsByTagName('road')->item(0)->nodeValue;
            $ret['city']      = $addressParts->getElementsByTagName('city')->item(0)->nodeValue;
            $ret['country']   = $addressParts->getElementsByTagName('country')->item(0)->nodeValue;

            return $ret;
        } else {
            return $this->getDefaults();
        }
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
     * @return string
     */
    protected function executeQuery($query)
    {
        if (null !== $this->getLocale()) {
            $query = sprintf('%s&accept-language=%s', $query, $this->getLocale());
        }

        return $this->getAdapter()->getContent($query);
    }
}
