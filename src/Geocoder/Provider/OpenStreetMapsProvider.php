<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Provider\ProviderInterface;

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

        $query   = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address));
        $content = $this->executeQuery($query);

        if (null === $content) {
            return $this->getDefaults();
        }

        $doc = new \DOMDocument();
        if (@$doc->loadXML($content)) {
            $maxRank      = 0;
            $bestNode     = null;
            $searchResult = $doc->getElementsByTagName('searchresults')->item(0);

            if (null === $searchResult) {
                return $this->getDefaults();
            }

            foreach ($searchResult->getElementsByTagName('place') as $node) {
                if ($node->getAttribute('place_rank') > $maxRank) {
                    $maxRank  = $node->getAttribute('place_rank');
                    $bestNode = $node;
                }
            }

            if (null === $bestNode) {
                return $this->getDefaults();
            }

            $bounds = null;
            $boundsAttr = $bestNode->getAttribute('boundingbox');
            if ($boundsAttr) {
                $bounds = array();
                list($bounds['south'], $bounds['north'], $bounds['west'], $bounds['east']) = explode(',', $boundsAttr);
            }

            $ret = $this->getDefaults();
            $ret['latitude']     = $bestNode->getAttribute('lat');
            $ret['longitude']    = $bestNode->getAttribute('lon');
            $ret['bounds']       = $bounds;
            $ret['zipcode']      = $this->getNodeValue($bestNode->getElementsByTagName('postcode'));
            $ret['county']       = $this->getNodeValue($bestNode->getElementsByTagName('county'));
            $ret['region']       = $this->getNodeValue($bestNode->getElementsByTagName('state'));
            $ret['streetNumber'] = $this->getNodeValue($bestNode->getElementsByTagName('house_number'));
            $ret['streetName']   = $this->getNodeValue($bestNode->getElementsByTagName('road'));
            $ret['city']         = $this->getNodeValue($bestNode->getElementsByTagName('city'));
            $ret['cityDistrict'] = $this->getNodeValue($bestNode->getElementsByTagName('suburb'));
            $ret['country']      = $this->getNodeValue($bestNode->getElementsByTagName('country'));
            $ret['countryCode']  = strtoupper($this->getNodeValue($bestNode->getElementsByTagName('country_code')));

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
        $query   = sprintf(self::REVERSE_ENDPOINT_URL, $coordinates[0], $coordinates[1]);
        $content = $this->executeQuery($query);

        if (null === $content) {
            return $this->getDefaults();
        }

        $doc = new \DOMDocument();
        if (@$doc->loadXML($content)) {
            $maxRank          = 0;
            $bestNode         = null;
            $searchResult     = $doc->getElementsByTagName('reversegeocode')->item(0);
            $addressParts     = $searchResult->getElementsByTagName('addressparts')->item(0);
            $result           = $searchResult->getElementsByTagName('result')->item(0);
            $ret              = $this->getDefaults();

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

    private function getNodeValue($element)
    {
        if ($element->length) {
            return $element->item(0)->nodeValue;
        }

        return null;
    }
}
