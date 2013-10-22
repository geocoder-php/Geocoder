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
class YandexProvider extends AbstractProvider implements LocaleAwareProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://geocode-maps.yandex.ru/1.x/?format=json&geocode=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://geocode-maps.yandex.ru/1.x/?format=json&geocode=%F,%F';

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

        $query = sprintf('%s&results=%d', $query, $this->getMaxResults());

        $content = $this->getAdapter()->getContent($query);
        $json    = (array) json_decode($content, true);

        if (empty($json) || '0' === $json['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found']) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $data = $json['response']['GeoObjectCollection']['featureMember'];

        $results = array();

        foreach ($data as $item) {
            $bounds = null;
            $details = array('pos' => ' ');

            array_walk_recursive(
                $item['GeoObject'],
                function ($value, $key) use (&$details) {$details[$key] = $value;}
            );

            if (! empty($details['lowerCorner'])) {
                $coordinates = explode(' ', $details['lowerCorner']);
                $bounds['south'] = $coordinates[1];
                $bounds['west']  = $coordinates[0];
            }

            if (! empty($details['upperCorner'])) {
                $coordinates = explode(' ', $details['upperCorner']);
                $bounds['north'] = $coordinates[1];
                $bounds['east']  = $coordinates[0];
            }

            $coordinates = explode(' ', $details['pos']);

            $results[] = array_merge($this->getDefaults(), array(
                'latitude'      => $coordinates[1],
                'longitude'     => $coordinates[0],
                'bounds'        => $bounds,
                'streetNumber'  => isset($details['PremiseNumber']) ? $details['PremiseNumber'] : null,
                'streetName'    => isset($details['ThoroughfareName']) ? $details['ThoroughfareName'] : null,
                'cityDistrict'  => isset($details['DependentLocalityName']) ? $details['DependentLocalityName'] : null,
                'city'          => isset($details['LocalityName']) ? $details['LocalityName'] : null,
                'region'        => isset($details['AdministrativeAreaName']) ? $details['AdministrativeAreaName'] : null,
                'country'       => isset($details['CountryName']) ? $details['CountryName'] : null,
                'countryCode'   => isset($details['CountryNameCode']) ? $details['CountryNameCode'] : null,
            ));
        }

        return $results;
    }
}
