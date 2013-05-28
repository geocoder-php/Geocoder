<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\NoResultException;
use Geocoder\Exception\UnsupportedException;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class FreeGeoIpProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const ENDPOINT_URL = 'http://freegeoip.net/json/%s';

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The FreeGeoIpProvider does not support Street addresses.');
        }

        if (in_array($address, array('127.0.0.1', '::1'))) {
            return array($this->getLocalhostDefaults());
        }

        $query = sprintf(self::ENDPOINT_URL, $address);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The FreeGeoIpProvider is not able to do reverse geocoding.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'free_geo_ip';
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function executeQuery($query)
    {
        $content = $this->getAdapter()->getContent($query);

        if (null === $content) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        $data = (array) json_decode($content);

        if (empty($data)) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        //it appears that for US states the region code is not returning the FIPS standard
        if ('US' === $data['country_code'] && isset($data['region_code']) && !is_numeric($data['region_code'])) {
            $newRegionCode = $this->stateToRegionCode($data['region_code']);
            $data['region_code'] = is_numeric($newRegionCode) ? $newRegionCode : null;
        }

        return array(array_merge($this->getDefaults(), array(
            'latitude'    => isset($data['latitude']) ? $data['latitude'] : null,
            'longitude'   => isset($data['longitude']) ? $data['longitude'] : null,
            'city'        => isset($data['city']) ? $data['city'] : null,
            'zipcode'     => isset($data['zipcode']) ? $data['zipcode'] : null,
            'region'      => isset($data['region_name']) ? $data['region_name'] : null,
            'regionCode'  => isset($data['region_code']) ? $data['region_code'] : null,
            'country'     => isset($data['country_name']) ? $data['country_name'] : null,
            'countryCode' => isset($data['country_code']) ? $data['country_code'] : null,
        )));
    }

    /**
     * Converts the state code to FIPS standard
     *
     * @param string $state
     *
     * @return string|integer The FIPS code or the state code if not found
     */
    private function stateToRegionCode($state)
    {
        $codes = $this->getRegionCodes();

        return array_key_exists($state, $codes) ? $codes[$state] : $state;
    }

    /**
     * Returns an array of state codes => FIPS codes
     * @see http://www.epa.gov/enviro/html/codes/state.html
     *
     * @return array
     */
    private function getRegionCodes()
    {
        return array(
            'AK' => 2, //ALASKA
            'AL' => 1, //ALABAMA
            'AR' => 5, //ARKANSAS
            'AS' => 60, //AMERICAN SAMOA
            'AZ' => 4, //ARIZONA
            'CA' => 6, //CALIFORNIA
            'CO' => 8, //COLORADO
            'CT' => 9, //CONNECTICUT
            'DC' => 11, //DISTRICT OF COLUMBIA
            'DE' => 10, //DELAWARE
            'FL' => 12, //FLORIDA
            'GA' => 13, //GEORGIA
            'GU' => 66, //GUAM
            'HI' => 15, //HAWAII
            'IA' => 19, //IOWA
            'ID' => 16, //IDAHO
            'IL' => 17, //ILLINOIS
            'IN' => 18, //INDIANA
            'KS' => 20, //KANSAS
            'KY' => 21, //KENTUCKY
            'LA' => 22, //LOUISIANA
            'MA' => 25, //MASSACHUSETTS
            'MD' => 24, //MARYLAND
            'ME' => 23, //MAINE
            'MI' => 26, //MICHIGAN
            'MN' => 27, //MINNESOTA
            'MO' => 29, //MISSOURI
            'MS' => 28, //MISSISSIPPI
            'MT' => 30, //MONTANA
            'NC' => 37, //NORTH CAROLINA
            'ND' => 38, //NORTH DAKOTA
            'NE' => 31, //NEBRASKA
            'NH' => 33, //NEW HAMPSHIRE
            'NJ' => 34, //NEW JERSEY
            'NM' => 35, //NEW MEXICO
            'NV' => 32, //NEVADA
            'NY' => 36, //NEW YORK
            'OH' => 39, //OHIO
            'OK' => 40, //OKLAHOMA
            'OR' => 41, //OREGON
            'PA' => 42, //PENNSYLVANIA
            'PR' => 72, //PUERTO RICO
            'RI' => 44, //RHODE ISLAND
            'SC' => 45, //SOUTH CAROLINA
            'SD' => 46, //SOUTH DAKOTA
            'TN' => 47, //TENNESSEE
            'TX' => 48, //TEXAS
            'UT' => 49, //UTAH
            'VA' => 51, //VIRGINIA
            'VI' => 78, //VIRGIN ISLANDS
            'VT' => 50, //VERMONT
            'WA' => 53, //WASHINGTON
            'WI' => 55, //WISCONSIN
            'WV' => 54, //WEST VIRGINIA
        );
    }
}
