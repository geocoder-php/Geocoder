<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder\Model\Query;

use Geocoder\Exception\InvalidArgument;
use Geocoder\Model\Bounds;
use Geocoder\Model\Coordinates;
use Geocoder\Provider\Provider;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class GeocodeQuery
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var Bounds
     */
    private $bounds;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var array
     */
    private $data;

    /**
     * @param Coordinates $coordinates
     */
    private function __construct($text)
    {
        if (empty($text)) {
            throw new InvalidArgument('Geocode query cannot be empty');
        }

        $this->text = $text;
        $this->data = [];
        $this->limit = Provider::MAX_RESULTS;
    }

    /**
     * @param $text
     *
     * @return GeocodeQuery
     */
    public static function create($text)
    {
        return new self($text);
    }

    /**
     * @param Bounds $bounds
     *
     * @return GeocodeQuery
     */
    public function withBounds(Bounds $bounds)
    {
        $new = clone $this;
        $new->bounds = $bounds;

        return $new;
    }

    /**
     * @param string $locale
     *
     * @return GeocodeQuery
     */
    public function withLocale($locale)
    {
        $new = clone $this;
        $new->locale = $locale;

        return $new;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function withLimit($limit)
    {
        $new = clone $this;
        $new->limit = $limit;

        return $new;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function withData($name, $value)
    {
        $new = clone $this;
        $new->data[$name] = $value;

        return $new;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return Bounds
     */
    public function getBounds()
    {
        return $this->bounds;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
