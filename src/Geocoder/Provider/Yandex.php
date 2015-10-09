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

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class Yandex extends AbstractHttpProvider implements LocaleAwareProvider
{
    use LocaleTrait;

    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode=%F,%F';

    /**
     * @var string
     */
    private $toponym;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $locale  A locale (optional).
     * @param string               $toponym Toponym biasing only for reverse geocoding (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $locale = null, $toponym = null)
    {
        parent::__construct($adapter);

        $this->locale  = $locale;
        $this->toponym = $toponym;
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Yandex provider does not support IP addresses, only street addresses.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address));

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        $query = sprintf(self::REVERSE_ENDPOINT_URL, $longitude, $latitude);

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
     */
    private function executeQuery($query)
    {
        if (null !== $this->getLocale()) {
            $query = sprintf('%s&lang=%s', $query, str_replace('_', '-', $this->getLocale()));
        }

        $query = sprintf('%s&results=%d', $query, $this->getLimit());

        $content = (string) $this->getAdapter()->get($query)->getBody();
        $json    = (array) json_decode($content, true);

        if (empty($json) || isset($json['error']) ||
            (isset($json['response']) &&  '0' === $json['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'])
        ) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        $data = $json['response']['GeoObjectCollection']['featureMember'];

        $results = [];
        foreach ($data as $item) {
            $bounds = null;
            $details = array('pos' => ' ');

            array_walk_recursive(
                $item['GeoObject'],

                /**
                 * @param string $value
                 */
                function ($value, $key) use (&$details) {$details[$key] = $value;}
            );

            if (! empty($details['lowerCorner'])) {
                $coordinates = explode(' ', $details['lowerCorner']);
                $bounds['south'] = (float) $coordinates[1];
                $bounds['west']  = (float) $coordinates[0];
            }

            if (! empty($details['upperCorner'])) {
                $coordinates = explode(' ', $details['upperCorner']);
                $bounds['north'] = (float) $coordinates[1];
                $bounds['east']  = (float) $coordinates[0];
            }

            $coordinates = explode(' ', $details['pos']);

            $adminLevels = [];
            foreach (['AdministrativeAreaName', 'SubAdministrativeAreaName'] as $i => $detail) {
                if (isset($details[$detail])) {
                    $adminLevels[] = ['name' => $details[$detail], 'level' => $i + 1];
                }
            }

            $results[] = array_merge($this->getDefaults(), array(
                'latitude'     => (float) $coordinates[1],
                'longitude'    => (float) $coordinates[0],
                'bounds'       => $bounds,
                'streetNumber' => isset($details['PremiseNumber']) ? $details['PremiseNumber'] : null,
                'streetName'   => isset($details['ThoroughfareName']) ? $details['ThoroughfareName'] : null,
                'subLocality'  => isset($details['DependentLocalityName']) ? $details['DependentLocalityName'] : null,
                'locality'     => isset($details['LocalityName']) ? $details['LocalityName'] : null,
                'adminLevels'  => $adminLevels,
                'country'      => isset($details['CountryName']) ? $details['CountryName'] : null,
                'countryCode'  => isset($details['CountryNameCode']) ? $details['CountryNameCode'] : null,
            ));
        }

        return $this->returnResults($results);
    }
}
