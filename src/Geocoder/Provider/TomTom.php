<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\InvalidCredentials;
use Geocoder\Exception\NoResult;
use Geocoder\Exception\UnsupportedOperation;
use Ivory\HttpAdapter\HttpAdapterInterface;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class TomTom extends AbstractHttpProvider implements LocaleAwareProvider
{
    use LocaleTrait;

    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'https://api.tomtom.com/lbs/services/geocode/4/geocode?key=%s&query=%s&maxResults=%d';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'https://api.tomtom.com/lbs/services/reverseGeocode/3/xml?key=%s&point=%F,%F';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $apiKey  An API key.
     * @param string               $locale  A locale (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey, $locale = null)
    {
        parent::__construct($adapter);

        $this->apiKey = $apiKey;
        $this->locale = $locale;
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided.');
        }

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The TomTom provider does not support IP addresses, only street addresses.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $this->apiKey, rawurlencode($address), $this->getLimit());

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No Map API Key provided.');
        }

        $query = sprintf(self::REVERSE_ENDPOINT_URL, $this->apiKey, $latitude, $longitude);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'tomtom';
    }

    /**
     * @param string $query
     */
    private function executeQuery($query)
    {
        if (null !== $this->getLocale()) {
            // Supported 2- character values are de, en, es, fr, it, nl, pl, pt, and sv.
            // Equivalent 3-character values are GER, ENG, SPA, FRE, ITA, DUT, POL, POR, and SWE.
            $query = sprintf('%s&language=%s', $query, substr($this->getLocale(), 0, 2));
        }

        $content = (string) $this->getAdapter()->get($query)->getBody();

        if (false !== stripos($content, "Developer Inactive")) {
            throw new InvalidCredentials('Map API Key provided is not valid.');
        }

        try {
            $xml = new \SimpleXmlElement($content);
        } catch (\Exception $e) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        $attributes = $xml->attributes();

        if (isset($attributes['count']) && 0 === (int) $attributes['count']) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        if (isset($attributes['errorCode'])) {
            if ('403' === (string) $attributes['errorCode']) {
                throw new InvalidCredentials('Map API Key provided is not valid.');
            }

            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        $data = isset($xml->geoResult) ? $xml->geoResult : $xml->reverseGeoResult;


        if (0 === count($data)) {
            return $this->returnResults([ $this->getResultArray($data) ]);
        }

        $results = [];
        foreach ($data as $item) {
            $results[] = $this->getResultArray($item);
        }

        return $this->returnResults($results);
    }

    private function getResultArray(\SimpleXmlElement $data)
    {
        return array_merge($this->getDefaults(), array(
            'latitude'    => isset($data->latitude) ? (double) $data->latitude : null,
            'longitude'   => isset($data->longitude) ? (double) $data->longitude : null,
            'streetName'  => isset($data->street) ? (string) $data->street : null,
            'locality'    => isset($data->city) ? (string) $data->city : null,
            'adminLevels' => isset($data->state) ? [['name' => (string) $data->state, 'level' => 1]] : [],
            'country'     => isset($data->country) ? (string) $data->country : null,
            'countryCode' => isset($data->countryISO3) ? (string) $data->countryISO3 : null,
        ));
    }
}
