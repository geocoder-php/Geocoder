<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Result;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class Geocoded implements ResultInterface, \ArrayAccess
{
    /**
     * @var double
     */
    protected $latitude = 0;

    /**
     * @var double
     */
    protected $longitude = 0;

    /**
     * @var array
     */
    protected $bounds = null;

    /**
     * @var string|int
     */
    protected $streetNumber = null;

    /**
     * @var string
     */
    protected $streetName = null;

    /**
     * @var string
     */
    protected $cityDistrict = null;

    /**
     * @var string
     */
    protected $city = null;

    /**
     * @var string
     */
    protected $zipcode = null;

    /**
     * @var string
     */
    protected $county = null;

    /**
     * @var string
     */
    protected $region = null;

    /**
     * @var string
     */
    protected $regionCode = null;

    /**
     * @var string
     */
    protected $country = null;

    /**
     * @var string
     */
    protected $countryCode = null;

    /**
     * {@inheritDoc}
     */
    public function getCoordinates()
    {
        return array($this->getLatitude(), $this->getLongitude());
    }

    /**
     * {@inheritDoc}
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * {@inheritDoc}
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * {@inheritDoc}
     */
    public function getBounds()
    {
        return $this->bounds;
    }

    /**
     * {@inheritDoc}
     */
    public function getStreetNumber()
    {
        return $this->streetNumber;
    }

    /**
     * {@inheritDoc}
     */
    public function getStreetName()
    {
        return $this->streetName;
    }

    /**
     * {@inheritDoc}
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * {@inheritDoc}
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * {@inheritDoc}
     */
    public function getCityDistrict()
    {
        return $this->cityDistrict;
    }

    /**
     * {@inheritDoc}
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * {@inheritDoc}
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * {@inheritDoc}
     */
    public function getRegionCode()
    {
        return $this->regionCode;
    }

    /**
     * {@inheritDoc}
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * {@inheritDoc}
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * {@inheritDoc}
     */
    public function fromArray(array $data = array())
    {
        if (isset($data['latitude'])) {
            $this->latitude = (double) $data['latitude'];
        }
        if (isset($data['longitude'])) {
            $this->longitude = (double) $data['longitude'];
        }
        if (isset($data['bounds']) && is_array($data['bounds'])) {
            $this->bounds = array(
                'south' => (double) $data['bounds']['south'],
                'west'  => (double) $data['bounds']['west'],
                'north' => (double) $data['bounds']['north'],
                'east'  => (double) $data['bounds']['east']
            );
        }
        if (isset($data['streetNumber'])) {
            $this->streetNumber = (string) $data['streetNumber'];
        }
        if (isset($data['streetName'])) {
            $this->streetName = $this->formatString($data['streetName']);
        }
        if (isset($data['city'])) {
            $this->city = $this->formatString($data['city']);
        }
        if (isset($data['zipcode'])) {
            $this->zipcode = (string) $data['zipcode'];
        }
        if (isset($data['cityDistrict'])) {
            $this->cityDistrict = $this->formatString($data['cityDistrict']);
        }
        if (isset($data['county'])) {
            $this->county = $this->formatString($data['county']);
        }
        if (isset($data['region'])) {
            $this->region = $this->formatString($data['region']);
        }
        if (isset($data['regionCode'])) {
            $this->regionCode = $this->upperize($data['regionCode']);
        }
        if (isset($data['country'])) {
            $this->country = $this->formatString($data['country']);
        }
        if (isset($data['countryCode'])) {
            $this->countryCode = $this->lowerize($data['countryCode']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return array(
            'latitude'      => $this->latitude,
            'longitude'     => $this->longitude,
            'bounds'        => $this->bounds,
            'streetNumber'  => $this->streetNumber,
            'streetName'    => $this->streetName,
            'zipcode'       => $this->zipcode,
            'city'          => $this->city,
            'cityDistrict'  => $this->cityDistrict,
            'county'        => $this->county,
            'region'        => $this->region,
            'regionCode'    => $this->regionCode,
            'country'       => $this->country,
            'countryCode'   => $this->countryCode,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return property_exists($this, $this->lowerize($offset)) && null !== $this->$offset;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        $offset = $this->lowerize($offset);

        return $this->offsetExists($offset) ? $this->$offset : null;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        $offset = $this->lowerize($offset);
        if ($this->offsetExists($offset)) {
            $this->$offset = $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        $offset = $this->lowerize($offset);
        if ($this->offsetExists($offset)) {
            $this->$offset = null;
        }
    }

    /**
     * Format a string data.
     *
     * @param string $str A string.
     * @return string
     */
    private function formatString($str)
    {
        if (extension_loaded('mbstring')) {
            $str = mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
        } else {
            $str = strtolower($str);
            $str = ucwords($str);
        }

        $str = str_replace('-', '- ', $str);
        $str = str_replace('- ', '-', $str);

        return $str;
    }

    private function lowerize($str)
    {
        if (extension_loaded('mbstring')) {
            $str = mb_strtolower($str, 'UTF-8');
        } else {
            $str = strtolower($str);
        }

        return $str;
    }

    private function upperize($str)
    {
        if (extension_loaded('mbstring')) {
            $str = mb_strtoupper($str, 'UTF-8');
        } else {
            $str = strtolower($str);
        }

        return $str;
    }
}
