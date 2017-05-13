<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider\Geonames;

use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\AdminLevelCollection;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\AbstractHttpProvider;
use Geocoder\Provider\LocaleAwareGeocoder;
use Geocoder\Provider\Provider;
use Http\Client\HttpClient;

/**
 * @author Giovanni Pirrotta <giovanni.pirrotta@gmail.com>
 */
final class Geonames extends AbstractHttpProvider implements LocaleAwareGeocoder, Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://api.geonames.org/searchJSON?q=%s&maxRows=%d&style=full&username=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://api.geonames.org/findNearbyPlaceNameJSON?lat=%F&lng=%F&style=full&maxRows=%d&username=%s';

    /**
     * @var string
     */
    private $username;

    /**
     * @param HttpClient $client   An HTTP adapter
     * @param string     $username Username login (Free registration at http://www.geonames.org/login)
     */
    public function __construct(HttpClient $client, $username)
    {
        parent::__construct($client);

        $this->username = $username;
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query)
    {
        $address = $query->getText();
        if (null === $this->username) {
            throw new InvalidCredentials('No username provided.');
        }

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Geonames provider does not support IP addresses.');
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address), $query->getLimit(), $this->username);

        return $this->executeQuery($url, $query->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query)
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();
        if (null === $this->username) {
            throw new InvalidCredentials('No username provided.');
        }

        $url = sprintf(self::REVERSE_ENDPOINT_URL, $latitude, $longitude, $query->getLimit(), $this->username);

        return $this->executeQuery($url, $query->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'geonames';
    }

    /**
     * @param string $query
     * @param string $locale
     */
    private function executeQuery($query, $locale)
    {
        if (null !== $locale) {
            // Locale code transformation: for example from it_IT to it
            $query = sprintf('%s&lang=%s', $query, substr($locale, 0, 2));
        }

        $request = $this->getMessageFactory()->createRequest('GET', $query);
        $content = (string) $this->getHttpClient()->sendRequest($request)->getBody();

        if (empty($content)) {
            throw InvalidServerResponse::create($query);
        }

        if (null === $json = json_decode($content)) {
            throw InvalidServerResponse::create($query);
        }

        if (isset($json->totalResultsCount) && empty($json->totalResultsCount)) {
            return new AddressCollection([]);
        }

        $data = $json->geonames;

        if (empty($data)) {
            return new AddressCollection([]);
        }

        $results = [];
        foreach ($data as $item) {
            $bounds = null;

            if (isset($item->bbox)) {
                $bounds = [
                    'south' => $item->bbox->south,
                    'west' => $item->bbox->west,
                    'north' => $item->bbox->north,
                    'east' => $item->bbox->east,
                ];
            }

            $adminLevels = [];

            for ($level = 1; $level <= AdminLevelCollection::MAX_LEVEL_DEPTH; ++$level) {
                $adminNameProp = 'adminName'.$level;
                $adminCodeProp = 'adminCode'.$level;
                if (!empty($item->$adminNameProp) || !empty($item->$adminCodeProp)) {
                    $adminLevels[] = [
                        'name' => empty($item->$adminNameProp) ? null : $item->$adminNameProp,
                        'code' => empty($item->$adminCodeProp) ? null : $item->$adminCodeProp,
                        'level' => $level,
                    ];
                }
            }

            $results[] = array_merge($this->getDefaults(), [
                'latitude' => isset($item->lat) ? $item->lat : null,
                'longitude' => isset($item->lng) ? $item->lng : null,
                'bounds' => $bounds,
                'locality' => isset($item->name) ? $item->name : null,
                'adminLevels' => $adminLevels,
                'country' => isset($item->countryName) ? $item->countryName : null,
                'countryCode' => isset($item->countryCode) ? $item->countryCode : null,
                'timezone' => isset($item->timezone->timeZoneId) ? $item->timezone->timeZoneId : null,
            ]);
        }

        return $this->returnResults($results);
    }
}
