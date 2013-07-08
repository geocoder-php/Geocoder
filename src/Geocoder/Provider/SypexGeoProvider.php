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
use Geocoder\Exception\UnsupportedException;
use Geocoder\Exception\NoResultException;

/**
 *
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
class SypexGeoProvider extends AbstractProvider implements ProviderInterface
{
    private $sypex;

    private $countryOnly;

    public function __construct($dbFile, $flags = null, $countryOnly = false)
    {
        // trigger autoload
        class_exists('SxGeo');
        if (null === $flags) {
            $flags = SXGEO_FILE;
        }
        $this->sypex = new \SxGeo($dbFile, $flags);
        $this->countryOnly = $countryOnly;
    }

    /**
     * {@inheritDoc}
     */
    public function getGeocodedData($address)
    {
        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedException('The Sypex Geo supports only IP addresses.');
        }

        if ($this->countryOnly) {
            $data = $this->sypex->getCountry($address);
            if ($data) {
                $data = array('country' => $data);
            }
        } else {
            $data = $this->sypex->getCityFull($address);
        }

        if (!$data) {
            throw new NoResultException('Could not find data');
        }

        $result = array('countryCode' => $data['country']);
        if (!$this->countryOnly) {
            $result += array(
                'region'    => $data['region_name'],
                'city'      => $data['city'],
                'latitude'  => $data['lat'],
                'longitude' => $data['lon'],
                'timezone'  => $data['timezone'],
            );
        }

        $result = array_merge($this->getDefaults(), array_filter($result));

        return array($result);
    }

    /**
     * {@inheritDoc}
     */
    public function getReversedData(array $coordinates)
    {
        throw new UnsupportedException('The Sypex Geo supports only IP addresses.');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sypexgeo';
    }
}
