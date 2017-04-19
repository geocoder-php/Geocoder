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
use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Exception\NoResult;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Exception\ZeroResults;
use Geocoder\Model\AdminLevelCollection;
use Http\Client\HttpClient;

/**
 * @author Giovanni Pirrotta <giovanni.pirrotta@gmail.com>
 */
final class Geonames extends AbstractHttpProvider implements LocaleAwareProvider
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
    const BASE_ENDPOINT_URL = 'http://api.geonames.org/%s?username=%s';

    use LocaleTrait;

    /**
     * @var string
     */
    private $username;

    /**
     * @var integer
     */
    private $type;

    /**
     * @param HttpClient $client   An HTTP adapter
     * @param string     $username Username login (Free registration at http://www.geonames.org/login)
     * @param string     $locale   A locale (optional)
     */
    public function __construct(HttpClient $client, $username, $locale = null)
    {
        parent::__construct($client);

        $this->username = $username;
        $this->locale   = $locale;
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($address)
    {
        if (null === $this->username) {
            throw new InvalidCredentials('No username provided.');
        }

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation('The Geonames provider does not support IP addresses.');
        }

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address), $this->getLimit(), $this->username);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        if (null === $this->username) {
            throw new InvalidCredentials('No username provided.');
        }

        $query = sprintf(self::REVERSE_ENDPOINT_URL, $latitude, $longitude, $this->getLimit(), $this->username);

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
     *  Obtain all countries of the world from geonames
     */
    public function getAllCountries()
    {
        $this->type = 3;
        $query = sprintf(self::BASE_ENDPOINT_URL, 'countryInfoJSON', $this->username);
        $query = sprintf('%s&maxRows=%d&startRow=%d&style=FULL', $query, 1000, 0);

        return $this->executeQuery($query);
    }

    /**
     * Obtain all states of a Country
     *
     * @param geonameID  Identifier of a Country in Geonames
     */
    public function getAllStatesFor($geonameID)
    {
        $this->type = 5;
        $query = sprintf(self::BASE_ENDPOINT_URL, 'childrenJSON', $this->username);
        $query = sprintf('%s&maxRows=%d&startRow=%d&geonameId=%d&style=FULL', $query, 1000, 0, $geonameID);

        return $this->executeQuery($query);
    }

    /**
     * Obtain all regions by state ID
     *
     * @param geonameID  Identifier of a Region of State in Geonames
     */
    public function getAllRegionsFor($geonameID)
    {
        $this->type = 5;
        $query = sprintf(self::BASE_ENDPOINT_URL, 'childrenJSON', $this->username);
        $query = sprintf('%s&maxRows=%d&startRow=%d&geonameId=%d&style=FULL', $query, 1000, 0, $geonameID);

        return $this->executeQuery($query);
    }

    /**
     * Obtain all cities with a Region ID
     *
     * @param geonameID  Identifier of a Region in Geonames
     */
    public function getAllCitiesFor($geonameID)
    {
        $this->type = 5;
        $query = sprintf(self::BASE_ENDPOINT_URL, 'childrenJSON', $this->username);
        $query = sprintf('%s&maxRows=%d&startRow=%d&geonameId=%d&style=FULL', $query, 1000, 0, $geonameID);

        return $this->executeQuery($query);
    }

    /**
     * Obtain the most population cities of a Country
     *
     * @param countryCode  It's an identifier on a Country Object in Geonames
     */
    public function getAllCitiesByCountryCode($countryCode)
    {
        $this->type = 4;
        $query = sprintf(self::BASE_ENDPOINT_URL, 'searchJSON', $this->username);
        
        $completeResultArray = [];
                
        $maxRows = 1000;
        $startRow = 0;
        $hasNext = 1;
        
        while($hasNext != 0)
        {
            $customQuery = sprintf('%s&country=%s&maxRows=%d&style=FULL&startRow=%d&cities=cities5000', $query, $countryCode, $maxRows, $startRow);

            
            $result = $this->executeQuery($customQuery);
            
            if (($result != null) && (count($result) > 0))
            {
                $completeResultArray = array_merge($completeResultArray, $result);

                $startRow = $startRow + 1000;
            }
            else
            {
                $hasNext = 0;
            }
        }

        return $completeResultArray;
    }

    /**
     * @param string $query
     */
    private function executeQuery($query)
    {
        if (null !== $this->getLocale()) {
            // Locale code transformation: for example from it_IT to it
            $query = sprintf('%s&lang=%s', $query, substr($this->getLocale(), 0, 2));
        }

        $content = (string) $this->getAdapter()->get($query)->getBody();

        if (empty($content)) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        if (null === $json = json_decode($content)) {
            throw new NoResult(sprintf('Could not execute query "%s".', $query));
        }

        if (isset($json->totalResultsCount) && empty($json->totalResultsCount)) {
            throw new NoResult(sprintf('No places found for query "%s".', $query));
        }
        
        if (isset($json->status->value)) {
            return null;
        }

        $data = $json->geonames;
        
        if ($this->type == 3 || 
            $this->type == 4 ||
            $this->type == 5)
        {
            return $data;
        }
        else
        {
            if (empty($data)) {
                throw new NoResult(sprintf('Could not execute query "%s".', $query));
            }
        
        $results = [];
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

            $adminLevels = [];

            for ($level = 1; $level <= AdminLevelCollection::MAX_LEVEL_DEPTH; ++ $level) {
                $adminNameProp = 'adminName' . $level;
                $adminCodeProp = 'adminCode' . $level;
                if (! empty($item->$adminNameProp) || ! empty($item->$adminCodeProp)) {
                    $adminLevels[] = [
                        'name' => empty($item->$adminNameProp) ? null : $item->$adminNameProp ,
                        'code' => empty($item->$adminCodeProp) ? null : $item->$adminCodeProp,
                        'level' => $level,
                    ];
                }
            }

            $results[] = array_merge($this->getDefaults(), [
                'latitude'    => isset($item->lat) ? $item->lat : null,
                'longitude'   => isset($item->lng) ? $item->lng : null,
                'bounds'      => $bounds,
                'locality'    => isset($item->name) ? $item->name : null,
                'adminLevels' => $adminLevels,
                'country'     => isset($item->countryName) ? $item->countryName : null,
                'countryCode' => isset($item->countryCode) ? $item->countryCode : null,
                'timezone'    => isset($item->timezone->timeZoneId)  ? $item->timezone->timeZoneId : null,
            ]);
        }

        return $this->returnResults($results);
        }
    }
}
