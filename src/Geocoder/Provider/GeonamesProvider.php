<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\InvalidCredentialsException;
use Geocoder\Exception\NoResultException;
use Geocoder\Exception\UnsupportedException;
use Geocoder\HttpAdapter\HttpAdapterInterface;

/**
 * @author Giovanni Pirrotta <giovanni.pirrotta@gmail.com>
 */
class GeonamesProvider extends AbstractProvider implements LocaleAwareProviderInterface
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
    private $username = null;

    /**
     * @param HttpAdapterInterface $adapter  An HTTP adapter.
     * @param string               $username Username login (Free registration at http://www.geonames.org/login)
     * @param string               $locale   A locale (optional).
     */
    public function __construct(HttpAdapterInterface $adapter, $username, $locale = null)
    {
        parent::__construct($adapter, $locale);
        $this->username = $username;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (null === $this->username) {
            throw new InvalidCredentialsException('No Username provided');
        }

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The GeonamesProvider does not support IP addresses.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address), $this->getMaxResults(), $this->username);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        if (null === $this->username) {
            throw new InvalidCredentialsException('No Username provided');
        }

        $query = sprintf(self::REVERSE_ENDPOINT_URL, $coordinates[0], $coordinates[1], $this->getMaxResults(), $this->username);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'geonames';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        if (null !== $this->getLocale()) {
            // Locale code transformation: for example from it_IT to it
            $query = sprintf('%s&lang=%s', $query, substr($this->getLocale(), 0, 2));
        }

        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        if (null === $json = json_decode($content)) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        if (isset($json->totalResultsCount) && empty($json->totalResultsCount)) {
            throw new NoResultException(sprintf('No places found for query %s', $query));
        }

        $data = $json->geonames;

        if (empty($data)) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $results = array();

        foreach ($data as $item) {
            $bounds = null;
            if (isset($item->bbox)) {
                $bounds = array(
                    'south' => $item->bbox->south,
                    'west'  => $item->bbox->west,
                    'north' => $item->bbox->north,
                    'east'  => $item->bbox->east
                );
            }

            $results[] = array_merge($this->getDefaults(), array(
                'latitude'    => isset($item->lat) ? $item->lat : null,
                'longitude'   => isset($item->lng) ? $item->lng : null,
                'bounds'      => $bounds,
                'city'        => isset($item->name) ? $item->name : null,
                'county'      => isset($item->adminName2) ? $item->adminName2 : null,
                'region'      => isset($item->adminName1) ? $item->adminName1 : null,
                'country'     => isset($item->countryName) ? $item->countryName : null,
                'countryCode' => isset($item->countryCode) ? $item->countryCode : null,
                'timezone'    => isset($item->timezone->timeZoneId)  ? $item->timezone->timeZoneId : null,
            ));
        }

        return $results;
    }
}
