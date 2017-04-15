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
 * @author Patrick Koetsier <patrick.koetsier@alliander.com>
 */
class PDOKProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://geodata.nationaalgeoregister.nl/geocoder/Geocoder?Request=geocode&zoekterm=%s';



    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The PDOKprovider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'PDOK';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    public function getGeocodedData($address)
    {

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The PDOKservice does not support IP addresses.');
        }

        $query = sprintf(self::ENDPOINT_URL, urlencode($address));
        
        $content = $this->getAdapter()->getContent($query);

        $xml = new \DOMDocument();
        if (!@$xml->loadXML($content)) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }
        $xml->loadXML($content);
        $content = $xml->saveXML();

        $xml = new \SimpleXMLElement($content);
        $xml->registerXPathNamespace('xls', 'http://www.opengis.net/xls');        
        $result = $xml->xpath('//@numberOfGeocodedAddresses');
        
        while(list( , $node) = each($result)) {
            $numberOfGeocodedAddresses = $node;
        }
                                
        
        if (isset($numberOfGeocodedAddresses) && 0 === $numberOfGeocodedAddresses) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $results = array();

        $xml->registerXPathNamespace('gml', 'http://www.opengis.net/gml');
                    
        for ($i = 0; $i < $numberOfGeocodedAddresses; $i++) {
            $positions = $xml->xpath('//gml:pos');
            $positions = explode(' ', $positions[$i]);

            $zipcode      = $xml->xpath('//xls:PostalCode');
            $city         = $xml->xpath('//xls:Place[@type="Municipality"]');
            $countryCode  = $xml->xpath('//@countryCode');
            $streetNumber = $xml->xpath('//xls:Building');
            $cityDistrict = $xml->xpath('//xls:Street');
            $region       = $xml->xpath('//xls:Place[@type="CountrySubdivision"]');

            $results[] = array_merge($this->getDefaults(), array(
                'latitude'     => isset($positions[0]) ? (string) $positions[0] : null,
                'longitude'    => isset($positions[1]) ? (string) $positions[1] : null,
                'streetNumber' => isset($streetNumber[$i]) ? (string) $streetNumber[$i]->attributes() : null,
                'streetName'   => isset($cityDistrict[$i]) ? (string) $cityDistrict[$i] : null,
                'city'         => isset($city[$i]) ? (string) $city[$i] : null,
                'zipcode'      => isset($zipcode[$i]) ? (string) $zipcode[$i] : null,
                'cityDistrict' => isset($cityDistrict[$i]) ? (string) $cityDistrict[$i] : null,
                'countryCode'  => isset($countryCode[$i]) ? (string) $countryCode[$i] : null,
                'county'       => isset($region[$i]) ? (string) $region[$i] : null,
            ));
        }

        return $results;
    }
}
                  
