<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\UnsupportedOperation;
use Ivory\HttpAdapter\HttpAdapterInterface;

/**
 * @author Germán Mauricio Muñoz <mauricio@mapalesoftware.com>
 */
final class GoogleMapsPlaces extends GoogleMaps
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'https://maps.googleapis.com/maps/api/place/textsearch/json?query=%s';

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $apiKey  Google Places API key
     * @param string               $locale  A locale (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey, $locale = null)
    {
        parent::__construct($adapter, $locale, null, true, $apiKey);
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($place)
    {
        // This API doesn't handle IPs
        if (filter_var($place, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The GoogleMapsPlaces provider does not support IP addresses, only text searches for places.');
        }

        $query = sprintf(
            self::ENDPOINT_URL,
            rawurlencode($place)
        );

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'google_maps_places';
    }

    protected function getResults($responseResults)
    {
        $results = [];

        foreach ($responseResults as $result) {
            $resultset = $this->getDefaults();

            // update coordinates
            $coordinates = $result->geometry->location;
            $resultset['latitude']  = $coordinates->lat;
            $resultset['longitude'] = $coordinates->lng;

            $results[] = array_merge($this->getDefaults(), $resultset);
        }

        return $results;
    }
}
