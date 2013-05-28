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
use Geocoder\Exception\UnsupportedException;
use Geocoder\Exception\InvalidCredentialsException;
use Geocoder\Exception\NoResultException;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class IGNOpenLSProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://gpp3-wxs.ign.fr/%s/geoportail/ols?output=xml&xls=';

    /**
     * @var string
     */
    const ENDPOINT_QUERY = '<xls:XLS xmlns:xls="http://www.opengis.net/xls" version="1.2"><xls:RequestHeader/><xls:Request methodName="LocationUtilityService" version="1.2" maximumResponses="%d"><xls:GeocodeRequest returnFreeForm="false"><xls:Address countryCode="StreetAddress"><xls:freeFormAddress>%s</xls:freeFormAddress></xls:Address></xls:GeocodeRequest></xls:Request></xls:XLS>';

    /**
     * @var string
     */
    private $apiKey = null;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $apiKey  An API key.
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey)
    {
        parent::__construct($adapter, null);

        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentialsException('No API Key provided');
        }

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The IGNOpenLSProvider does not support IP addresses.');
        }

        $query   = sprintf('%s%s',
            sprintf(self::ENDPOINT_URL, $this->apiKey),
            urlencode(sprintf(self::ENDPOINT_QUERY, $this->getMaxResults(), $address))
        );
        $content = $this->getAdapter()->getContent($query);

        $doc = new \DOMDocument;
        if (!@$doc->loadXML($content)) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $xml = new \SimpleXMLElement($content);

        if (isset($xml->ErrorList->Error) || null === $xml->Response->GeocodeResponse) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $numberOfGeocodedAddresses = (int) $xml->Response
            ->GeocodeResponse
            ->GeocodeResponseList['numberOfGeocodedAddresses'];

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
            $bbox         = $xml->xpath('//xls:Place[@type="Bbox"]');
            $streetNumber = $xml->xpath('//xls:Building');
            $cityDistrict = $xml->xpath('//xls:Street');

            $bounds = null;
            if (isset($bbox[$i])) {
                list($bounds['west'], $bounds['south'], $bounds['east'], $bounds['north']) = explode(';', $bbox[$i]);
            }

            $results[] = array_merge($this->getDefaults(), array(
                'latitude'     => isset($positions[0]) ? (float) $positions[0] : null,
                'longitude'    => isset($positions[1]) ? (float) $positions[1] : null,
                'bounds'       => $bounds,
                'streetNumber' => isset($streetNumber[$i]) ? (string) $streetNumber[$i]->attributes() : null,
                'streetName'   => isset($cityDistrict[$i]) ? (string) $cityDistrict[$i] : null,
                'city'         => isset($city[$i]) ? (string) $city[$i] : null,
                'zipcode'      => isset($zipcode[$i]) ? (string) $zipcode[$i] : null,
                'cityDistrict' => isset($cityDistrict[$i]) ? (string) $cityDistrict[$i] : null,
                'country'      => 'France',
                'countryCode'  => 'FR',
                'timezone'     => 'Europe/Paris'
            ));
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The IGNOpenLSProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'ign_openls';
    }
}
