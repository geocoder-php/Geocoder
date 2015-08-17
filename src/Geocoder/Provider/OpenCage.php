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
 * @author mtm <mtm@opencagedata.com>
 */
class OpenCage extends AbstractHttpProvider implements LocaleAwareProvider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = '%s://api.opencagedata.com/geocode/v1/json?key=%s&query=%s&limit=%d&pretty=1';

    use LocaleTrait;

    /**
     * @var string
     */
    private $scheme;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param HttpAdapterInterface $adapter An HTTP adapter.
     * @param string               $apiKey  An API key.
     * @param bool                 $useSsl  Whether to use an SSL connection (optional).
     * @param string|null          $locale  A locale (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $apiKey, $useSsl = false, $locale = null)
    {
        parent::__construct($adapter);

        $this->apiKey = $apiKey;
        $this->scheme = $useSsl ? 'https' : 'http';
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function geocode($address)
    {
        if (null === $this->apiKey) {
            throw new InvalidCredentials('No API Key provided.');
        }

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The OpenCage provider does not support IP addresses, only street addresses.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $this->scheme, $this->apiKey, urlencode($address), $this->getLimit());

        return $this->executeQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function reverse($latitude, $longitude)
    {
        $address = sprintf('%f, %f', $latitude, $longitude);

        return $this->geocode($address);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'opencage';
    }

    /**
     * @param $query
     * @return \Geocoder\Model\AddressCollection
     */
    private function executeQuery($query)
    {
        if (null !== $this->getLocale()) {
            $query = sprintf('%s&language=%s', $query, $this->getLocale());
        }

        $content = (string) $this->getAdapter()->get($query)->getBody();

        if (empty($content)) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        $json = json_decode($content, true);

        if (!isset($json['total_results']) || $json['total_results'] == 0) {
            throw new NoResult(sprintf('Could not find results for query "%s".', $query));
        }

        $locations = $json['results'];

        if (empty($locations)) {
            throw new NoResult(sprintf('Could not find results for query "%s".', $query));
        }

        $results = [];
        foreach ($locations as $location) {
            $bounds = [];
            if (isset($location['bounds'])) {
                $bounds = [
                    'south' => $location['bounds']['southwest']['lat'],
                    'west' => $location['bounds']['southwest']['lng'],
                    'north' => $location['bounds']['northeast']['lat'],
                    'east' => $location['bounds']['northeast']['lng'],
                ];
            }

            $comp = $location['components'];

            $adminLevels = [];
            foreach (['state', 'county'] as $i => $component) {
                if (isset($comp[$component])) {
                    $adminLevels[] = ['name' => $comp[$component], 'level' => $i + 1];
                }
            }

            $results[] = array_merge($this->getDefaults(), array(
                'latitude' => $location['geometry']['lat'],
                'longitude' => $location['geometry']['lng'],
                'bounds' => $bounds ?: [],
                'streetNumber' => isset($comp['house_number']) ? $comp['house_number'] : null,
                'streetName' => $this->guessStreetName($comp),
                'subLocality' => $this->guessSubLocality($comp),
                'locality' => $this->guessLocality($comp),
                'postalCode' => isset($comp['postcode']) ? $comp['postcode']     : null,
                'adminLevels' => $adminLevels,
                'country' => isset($comp['country']) ? $comp['country']      : null,
                'countryCode' => isset($comp['country_code']) ? strtoupper($comp['country_code']) : null,
                'timezone' => isset($location['annotations']['timezone']['name']) ? $location['annotations']['timezone']['name'] : null,
            ));
        }

        return $this->returnResults($results);
    }

    /**
     * @param array $components
     *
     * @return null|string
     */
    protected function guessLocality(array $components)
    {
        $localityKeys = array('city', 'town' , 'village', 'hamlet');

        return $this->guessBestComponent($components, $localityKeys);
    }

    /**
     * @param array $components
     *
     * @return null|string
     */
    protected function guessStreetName(array $components)
    {
        $streetNameKeys = array('road', 'street', 'street_name', 'residential');

        return $this->guessBestComponent($components, $streetNameKeys);
    }

    /**
     * @param array $components
     *
     * @return null|string
     */
    protected function guessSubLocality(array $components)
    {
        $subLocalityKeys = array('suburb', 'neighbourhood', 'city_district');

        return $this->guessBestComponent($components, $subLocalityKeys);
    }

    /**
     * @param array $components
     * @param array $keys
     *
     * @return null|string
     */
    protected function guessBestComponent(array $components, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($components[$key]) && !empty($components[$key])) {
                return $components[$key];
            }
        }

        return null;
    }
}
