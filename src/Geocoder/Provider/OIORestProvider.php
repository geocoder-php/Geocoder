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
 * @author Antoine Corcy <contact@sbin.dk>
 */
class OIORestProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://geo.oiorest.dk/adresser/%s.json';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://geo.oiorest.dk/adresser/%F,%F.json';

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The OIORestProvider does not support IP addresses.');
        }

        // format address
        $address = preg_replace('/([a-zæøåÆØÅ]+) (\d+), (\d{4}) ([a-zæøåÆØÅ\ ])+/i', '${1},${2},${3}', $address);
        $address = rawurlencode($address);

        $query = sprintf(self::GEOCODE_ENDPOINT_URL, $address);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        $query = sprintf(self::REVERSE_ENDPOINT_URL, $coordinates[0], $coordinates[1]);

        return $this->executeQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'oio_rest';
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

        $data = (array) json_decode($content, true);

        if (empty($data) || false === $data) {
            throw new NoResultException(sprintf('Could not execute query %s', $query));
        }

        return array_merge($this->getDefaults(), array(
            'latitude'     => isset($data['wgs84koordinat']['bredde']) ? $data['wgs84koordinat']['bredde'] : null,
            'longitude'    => isset($data['wgs84koordinat']['længde']) ? $data['wgs84koordinat']['længde'] : null,
            'streetNumber' => isset($data['husnr']) ? $data['husnr'] : null,
            'streetName'   => isset($data['vejnavn']['navn']) ? $data['vejnavn']['navn'] : null,
            'city'         => isset($data['postnummer']['navn']) ? $data['postnummer']['navn'] : null,
            'zipcode'      => isset($data['postnummer']['nr']) ? $data['postnummer']['nr'] : null,
            'cityDistrict' => isset($data['kommune']['navn']) ? $data['kommune']['navn'] : null,
            'region'       => isset($data['region']['navn']) ? $data['region']['navn'] : null,
            'regionCode'   => isset($data['region']['nr']) ? $data['region']['nr'] : null,
            'country'      => 'Denmark',
            'countryCode'  => 'DK',
            'timezone'     => 'Europe/Copenhagen'
        ));
    }
}
