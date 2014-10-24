<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Provider;

use Geocoder\Exception\NoResult;
use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\HttpAdapter\GeoIP2Adapter;
use Geocoder\HttpAdapter\HttpAdapterInterface;
use GeoIp2\Exception\AddressNotFoundException;
use GeoIp2\Model\City;

/**
 * @author Jens Wiese <jens@howtrueisfalse.de>
 */
class GeoIP2 extends AbstractProvider implements Provider
{
    /**
     * {@inheritdoc}
     */
    public function __construct(HttpAdapterInterface $adapter, $locale = 'en')
    {
        if (false === $adapter instanceof GeoIP2Adapter) {
            throw new InvalidArgument(
                'GeoIP2Adapter is needed in order to access the GeoIP2 service.'
            );
        }

        parent::__construct($adapter, $locale);
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (false === filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation(sprintf('The %s does not support street addresses.', __CLASS__));
        }

        if ('127.0.0.1' === $address) {
            return $this->getLocalhostDefaults();
        }

        $result = json_decode($this->executeQuery($address));

        //Try to extract the region name and code
        $region = null;
        $regionCode = null;
        if (isset($result->subdivisions) && is_array($result->subdivisions) && !empty($result->subdivisions)) {
            $lastSubdivision = array_pop($result->subdivisions);

            $region = (isset($lastSubdivision->names->{$this->locale}) ? $lastSubdivision->names->{$this->locale} : null);
            $regionCode = (isset($lastSubdivision->iso_code) ? $lastSubdivision->iso_code : null);
        }

        return array($this->fixEncoding(array_merge($this->getDefaults(), array(
            'countryCode' => (isset($result->country->iso_code) ? $result->country->iso_code : null),
            'country'     => (isset($result->country->names->{$this->locale}) ? $result->country->names->{$this->locale} : null),
            'locality'    => (isset($result->city->names->{$this->locale}) ? $result->city->names->{$this->locale} : null),
            'latitude'    => (isset($result->location->latitude) ? $result->location->latitude : null),
            'longitude'   => (isset($result->location->longitude) ? $result->location->longitude : null),
            'timezone'    => (isset($result->location->timezone) ? $result->location->timezone : null),
            'postalCode'  => (isset($result->location->postalcode) ? $result->location->postalcode : null),
            'region'      => $region,
            'regionCode'  => $regionCode
        ))));
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedOperation(sprintf('The %s is not able to do reverse geocoding.', __CLASS__));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'maxmind_geoip2';
    }

    /**
     * @param  string                       $address
     * @throws \Geocoder\Exception\NoResult
     * @return City
     */
    private function executeQuery($address)
    {
        $uri = sprintf('file://geoip?%s', $address);

        try {
            $result = $this->getAdapter()->setLocale($this->locale)->getContent($uri);
        } catch (AddressNotFoundException $e) {
            throw new NoResult(sprintf('No results found for IP address %s', $address));
        }

        return $result;
    }

}
