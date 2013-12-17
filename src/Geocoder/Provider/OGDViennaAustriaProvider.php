<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\UnsupportedException;
use Geocoder\Exception\NoResultException;

/**
 * @author Robert Harm <www.harm.co.at>
 * Data source: City of Vienna, http://data.wien.gv.at
 */
class OGDViennaAustriaProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://data.wien.gv.at/daten/OGDAddressService.svc/GetAddressInfo?CRS=EPSG:4326&Address=%s';

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The OGDViennaAustriaProvider does not support IP addresses.');
        }

        $query = sprintf(self::ENDPOINT_URL, urlencode($address));

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The OGDViennaAustriaProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'ogdvienna_at';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (!$data) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $data = json_decode($content, true);

        if (empty($data) || false === $data) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $bounds = array(
            'south' => isset($data['features'][0]['bbox'][0]) ? $data['features'][0]['bbox'][0] : null,
            'west'  => isset($data['features'][0]['bbox'][1]) ? $data['features'][0]['bbox'][1] : null,
            'north' => isset($data['features'][0]['bbox'][2]) ? $data['features'][0]['bbox'][3] : null,
            'east'  => isset($data['features'][0]['bbox'][3]) ? $data['features'][0]['bbox'][2] : null,
        );

        return array(array_merge($this->getDefaults(), array(
            'longitude'    => isset($data['features'][0]['geometry']['coordinates'][0]) ? $data['features'][0]['geometry']['coordinates'][0] : null,
            'latitude'     => isset($data['features'][0]['geometry']['coordinates'][1]) ? $data['features'][0]['geometry']['coordinates'][1] : null,
            'bounds'       => $bounds,
            'streetNumber' => NULL, //info: zB 1/a - not available yet
            'streetName'   => isset($data['features'][0]['properties']['Adresse']) ? $data['features'][0]['properties']['Adresse'] : null,
            'cityDistrict' => NULL, //info: z.B. Donaustadt - not available yet
            'city'         => isset($data['features'][0]['geometry']['coordinates'][0]) ? 'Vienna' : null,
            'zipcode'      => isset($data['features'][0]['properties']['PLZ']) ? $data['features'][0]['properties']['PLZ'] : null,
            'county'       => NULL, //info: ??? - not available yet
            'countyCode'   => isset($data['features'][0]['properties']['Zaehlbezirk']) ? $data['features'][0]['properties']['Zaehlbezirk'] : null,
            'region'       => isset($data['features'][0]['geometry']['coordinates'][0]) ? 'VIENNA' : null,
            'regionCode'   => isset($data['features'][0]['geometry']['coordinates'][0]) ? 'VIENNA' : null,
            'country'      => isset($data['features'][0]['geometry']['coordinates'][0]) ? 'Austria' : null,
            'countryCode'  => isset($data['features'][0]['geometry']['coordinates'][0]) ? 'AT' : null,
            'timezone'     => isset($data['features'][0]['geometry']['coordinates'][0]) ? 'Europe/Vienna' : null,
        )));
    }
}