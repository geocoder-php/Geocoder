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
    const ENDPOINT_URL = 'http://gpp3-wxs.ign.fr/%s/geoportail/ols?output=json&xls=';

    /**
     * @var string
     */
    const ENDPOINT_QUERY = '<xls:XLS xmlns:xls="http://www.opengis.net/xls" version="1.2"><xls:RequestHeader/><xls:Request methodName="LocationUtilityService" version="1.2" maximumResponses="1"><xls:GeocodeRequest returnFreeForm="false"><xls:Address countryCode="StreetAddress"><xls:freeFormAddress>%s</xls:freeFormAddress></xls:Address></xls:GeocodeRequest></xls:Request></xls:XLS>';

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

        $query   = sprintf(self::ENDPOINT_URL, $this->apiKey) . urlencode(sprintf(self::ENDPOINT_QUERY, $address));
        $content = $this->getAdapter()->getContent($query);
        $data    = (array) json_decode($content, true);

        if (empty($data) || null === $data['xml']) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        if (200 !== $data['http']['status'] || null !== $data['http']['error']) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $xpath = new \SimpleXMLElement($data['xml']);
        $xpath->registerXPathNamespace('gml', 'http://www.opengis.net/gml');
        $positions = $xpath->xpath('//gml:pos');
        $positions = explode(' ', $positions[0]);

        $xpath->registerXPathNamespace('xls', 'http://www.opengis.net/xls');
        $zipcode      = $xpath->xpath('//xls:PostalCode');
        $city         = $xpath->xpath('//xls:Place[@type="Municipality"]');
        $streetNumber = $xpath->xpath('//xls:Building');
        $cityDistrict = $xpath->xpath('//xls:Street');

        return array_merge($this->getDefaults(), array(
            'latitude'     => isset($positions[0]) ? (float) $positions[0] : null,
            'longitude'    => isset($positions[1]) ? (float) $positions[1] : null,
            'streetNumber' => isset($streetNumber[0]) ? (string) $streetNumber[0]->attributes() : null,
            'streetName'   => isset($cityDistrict[0]) ? (string) $cityDistrict[0] : null,
            'city'         => isset($city[0]) ? (string) $city[0] : null,
            'zipcode'      => isset($zipcode[0]) ? (string) $zipcode[0] : null,
            'cityDistrict' => isset($cityDistrict[0]) ? (string) $cityDistrict[0] : null,
            'country'      => 'France',
            'countryCode'  => 'FR',
            'timezone'     => 'Europe/Paris'
        ));
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
