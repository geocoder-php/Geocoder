<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Query;

use Geocoder\Geocoder;
use Geocoder\Model\Coordinates;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class ReverseQuery
{
    /**
     * @var Coordinates
     */
    private $coordinates;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var array
     */
    private $data;

    /**
     * @param Coordinates $coordinates
     */
    private function __construct(Coordinates $coordinates)
    {
        $this->coordinates = $coordinates;
        $this->data = [];
        $this->limit = Geocoder::DEFAULT_RESULT_LIMIT;
    }

    /**
     * @param Coordinates $coordinates
     *
     * @return ReverseQuery
     */
    public static function create(Coordinates $coordinates)
    {
        return new self($coordinates);
    }

    /**
     * @param float $latitude
     * @param float $longitude
     *
     * @return ReverseQuery
     */
    public static function fromCoordinates($latitude, $longitude)
    {
        return new self(new Coordinates($latitude, $longitude));
    }

    /**
     * @param int $limit
     *
     * @return ReverseQuery
     */
    public function withLimit($limit)
    {
        $new = clone $this;
        $new->limit = $limit;

        return $new;
    }

    /**
     * @param string $locale
     *
     * @return ReverseQuery
     */
    public function withLocale($locale)
    {
        $new = clone $this;
        $new->locale = $locale;

        return $new;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return ReverseQuery
     */
    public function withData($name, $value)
    {
        $new = clone $this;
        $new->data[$name] = $value;

        return $new;
    }

    /**
     * @return Coordinates
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function getData($name, $default = null)
    {
        if (!array_key_exists($name, $this->data)) {
            return $default;
        }

        return $this->data[$name];
    }

    /**
     * @return array
     */
    public function getAllData()
    {
        return $this->data;
    }
}
