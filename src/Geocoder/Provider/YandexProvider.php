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
use Geocoder\Exception\NoResultException;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class YandexProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://geocode-maps.yandex.ru/1.x/?results=1&format=json&geocode=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://geocode-maps.yandex.ru/1.x/?results=1&format=json&geocode=%F,%F';

    /**
     * @var string
     */
    private $toponym = null;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $locale  A locale (optional).
     * @param string               $toponym Toponym biasing only for reverse geocoding (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $locale = null, $toponym = null)
    {
        parent::__construct($adapter, $locale);

        $this->toponym = $toponym;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The YandexProvider does not support IP addresses.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address));

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        $query = sprintf(self::REVERSE_ENDPOINT_URL, $coordinates[1], $coordinates[0]);

        if (null !== $this->toponym) {
            $query = sprintf('%s&kind=%s', $query, $this->toponym);
        }

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'yandex';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        if (null !== $this->getLocale()) {
            $query = sprintf('%s&lang=%s', $query, str_replace('_', '-', $this->getLocale()));
        }

        $content = $this->getAdapter()->getContent($query);
        $result  = (array) json_decode($content, true);

        if (empty($result) || '0' === $result['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found']) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $result = $result['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'];

        $country        = $result['metaDataProperty']['GeocoderMetaData']['AddressDetails']['Country'];
        $addressDetails = isset($country['AdministrativeArea']) ? $country['AdministrativeArea'] : $country;
        $locality       = isset($addressDetails['Locality']['Thoroughfare']) ? $addressDetails['Locality']['Thoroughfare'] : null;
        $coordinates    = explode(' ', $result['Point']['pos']);
        $bounds         = null;
        $lowerCorner    = explode(' ', $result['boundedBy']['Envelope']['lowerCorner']);
        $upperCorner    = explode(' ', $result['boundedBy']['Envelope']['upperCorner']);
        $bounds         = array(
            'south' => isset($lowerCorner[1]) ? $lowerCorner[1] : null,
            'west'  => isset($lowerCorner[0]) ? $lowerCorner[0] : null,
            'north' => isset($upperCorner[1]) ? $upperCorner[1] : null,
            'east'  => isset($upperCorner[0]) ? $upperCorner[0] : null
        );

        return array_merge($this->getDefaults(), array(
            'latitude'      => isset($coordinates[1]) ? $coordinates[1] : null,
            'longitude'     => isset($coordinates[0]) ? $coordinates[0] : null,
            'bounds'        => $bounds,
            'streetNumber'  => isset($locality['Premise']['PremiseNumber']) ? $locality['Premise']['PremiseNumber'] : null,
            'streetName'    => isset($locality['ThoroughfareName']) ? $locality['ThoroughfareName'] : null,
            'cityDistrict'  => isset($addressDetails['AdministrativeAreaName']) ? $addressDetails['AdministrativeAreaName'] : null,
            'country'       => isset($country['CountryName']) ? $country['CountryName'] : null,
            'countryCode'   => isset($country['CountryNameCode']) ? $country['CountryNameCode'] : null,
        ));
    }
}
