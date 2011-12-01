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
     * @var int
     */
    protected $streetNumber = null;

    /**
     * @var string
     */
    protected $streetName = null;

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
    protected $country = null;

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
    public function getCountry()
    {
        return $this->country;
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
        if (isset($data['streetNumber'])) {
            $this->streetNumber = (int) $data['streetNumber'];
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
        if (isset($data['county'])) {
            $this->county = $this->formatString($data['county']);
        }
        if (isset($data['region'])) {
            $this->region = $this->formatString($data['region']);
        }
        if (isset($data['country'])) {
            $this->country = $this->formatString($data['country']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return property_exists($this, strtolower($offset));
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        $offset = strtolower($offset);

        return $this->offsetExists($offset) ? $this->$offset : null;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        $offset = strtolower($offset);
        if ($this->offsetExists($offset)) {
            $this->$offset = $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        $offset = strtolower($offset);
        if ($this->offsetExists($offset)) {
            $this->$offset = null;
        }
    }

    /**
     * Format a string data.
     *
     * @param string $str   A string.
     * @return string
     */
    private function formatString($str)
    {
        $str = strtolower($str);
        $str = str_replace('-', '- ', $str);
        $str = ucwords($str);

        return str_replace('- ', '-', $str);
    }
}
