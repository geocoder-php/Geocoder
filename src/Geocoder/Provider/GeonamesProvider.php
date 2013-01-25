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
class GeonamesProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://api.geonames.org/searchJSON?q=%s&maxRows=1&style=full&username=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://api.geonames.org/findNearbyPlaceNameJSON?lat=%F&lng=%F&style=full&maxRows=1&username=%s';

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

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address), $this->username);

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

        $query = sprintf(self::REVERSE_ENDPOINT_URL, $coordinates[0], $coordinates[1], $this->username);

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

        $json = json_decode($content);

        if (isset($json->totalResultsCount) && ($json->totalResultsCount === 0)) {
            throw new NoResultException(sprintf('No places found for query %s', $query));
        }

        if (isset($json->geonames) && !(empty($json->geonames))) {
            $data = (array) $json->geonames[0];
        } else {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $bounds = null;
        if (isset($data['bbox'])) {
            $bounds = array(
                'south' => $data['bbox']->south,
                'west'  => $data['bbox']->west,
                'north' => $data['bbox']->north,
                'east'  => $data['bbox']->east
            );
        }

        return array_merge($this->getDefaults(), array(
            'latitude'      => isset($data['lat']) ? $data['lat'] : null,
            'longitude'     => isset($data['lng']) ? $data['lng'] : null,
            'bounds'        => $bounds,
            'city'          => isset($data['name']) ? $data['name'] : null,
            'county'        => isset($data['adminName2']) ? $data['adminName2'] : null,
            'region'        => isset($data['adminName1']) ? $data['adminName1'] : null,
            'country'       => isset($data['countryName']) ? $data['countryName'] : null,
            'countryCode'   => isset($data['countryCode']) ? $data['countryCode'] : null,
            'timezone'      => isset($data['timezone']->timeZoneId)  ? $data['timezone']->timeZoneId : null,
        ));
    }
}
