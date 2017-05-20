<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Yandex;

use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\AddressCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\AbstractHttpProvider;
use Geocoder\Provider\LocaleAwareGeocoder;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
final class Yandex extends AbstractHttpProvider implements LocaleAwareGeocoder, Provider
{
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
     * @param HttpClient $client  an HTTP adapter
     * @param string     $toponym toponym biasing only for reverse geocoding (optional)
     */
    public function __construct(HttpClient $client, $toponym = null)
    {
        parent::__construct($client);

        $this->toponym = $toponym;
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query)
    {
        $address = $query->getText();
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Yandex provider does not support IP addresses, only street addresses.');
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address));

        return $this->executeQuery($url, $query->getLocale(), $query->getLimit());
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query)
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();
        $url = sprintf(self::REVERSE_ENDPOINT_URL, $longitude, $latitude);

        if (null !== $this->toponym) {
            $url = sprintf('%s&kind=%s', $url, $this->toponym);
        }

        return $this->executeQuery($url, $query->getLocale(), $query->getLimit());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'yandex';
    }

    /**
     * @param string $url
     * @param string $locale
     * @param int    $limit
     */
    private function executeQuery($url, $locale, $limit)
    {
        if (null !== $locale) {
            $url = sprintf('%s&lang=%s', $url, str_replace('_', '-', $locale));
        }

        $url = sprintf('%s&results=%d', $url, $limit);
        $content = $this->getUrlContents($url);
        $json = json_decode($content, true);

        if (empty($json) || isset($json['error']) ||
            (isset($json['response']) && '0' === $json['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'])
        ) {
            return new AddressCollection([]);
        }

        $data = $json['response']['GeoObjectCollection']['featureMember'];

        $results = [];
        foreach ($data as $item) {
            $bounds = null;
            $details = ['pos' => ' '];

            array_walk_recursive(
                $item['GeoObject'],

                /**
                 * @param string $value
                 */
                function ($value, $key) use (&$details) {
                    $details[$key] = $value;
                }
            );

            if (!empty($details['lowerCorner'])) {
                $coordinates = explode(' ', $details['lowerCorner']);
                $bounds['south'] = (float) $coordinates[1];
                $bounds['west'] = (float) $coordinates[0];
            }

            if (!empty($details['upperCorner'])) {
                $coordinates = explode(' ', $details['upperCorner']);
                $bounds['north'] = (float) $coordinates[1];
                $bounds['east'] = (float) $coordinates[0];
            }

            $coordinates = explode(' ', $details['pos']);

            $adminLevels = [];
            foreach (['AdministrativeAreaName', 'SubAdministrativeAreaName'] as $i => $detail) {
                if (isset($details[$detail])) {
                    $adminLevels[] = ['name' => $details[$detail], 'level' => $i + 1];
                }
            }

            $results[] = array_merge($this->getDefaults(), [
                'latitude' => (float) $coordinates[1],
                'longitude' => (float) $coordinates[0],
                'bounds' => $bounds,
                'streetNumber' => isset($details['PremiseNumber']) ? $details['PremiseNumber'] : null,
                'streetName' => isset($details['ThoroughfareName']) ? $details['ThoroughfareName'] : null,
                'subLocality' => isset($details['DependentLocalityName']) ? $details['DependentLocalityName'] : null,
                'locality' => isset($details['LocalityName']) ? $details['LocalityName'] : null,
                'adminLevels' => $adminLevels,
                'country' => isset($details['CountryName']) ? $details['CountryName'] : null,
                'countryCode' => isset($details['CountryNameCode']) ? $details['CountryNameCode'] : null,
            ]);
        }

        return $this->returnResults($results);
    }
}
