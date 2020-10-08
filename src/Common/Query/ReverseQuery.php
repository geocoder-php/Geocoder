<?php

declare(strict_types=1);

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
final class ReverseQuery implements Query
{
    /**
     * @var Coordinates
     */
    private $coordinates;

    /**
     * @var string|null
     */
    private $locale;

    /**
     * @var int
     */
    private $limit = Geocoder::DEFAULT_RESULT_LIMIT;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param Coordinates $coordinates
     */
    private function __construct(Coordinates $coordinates)
    {
        $this->coordinates = $coordinates;
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
    public static function fromCoordinates($latitude, $longitude): self
    {
        return new self(new Coordinates($latitude, $longitude));
    }

    /**
     * @param Coordinates $coordinates
     *
     * @return ReverseQuery
     */
    public function withCoordinates(Coordinates $coordinates): self
    {
        $new = clone $this;
        $new->coordinates = $coordinates;

        return $new;
    }

    /**
     * @param int $limit
     *
     * @return ReverseQuery
     */
    public function withLimit(int $limit): self
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
    public function withLocale(string $locale): self
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
    public function withData(string $name, $value): self
    {
        $new = clone $this;
        $new->data[$name] = $value;

        return $new;
    }

    /**
     * @return Coordinates
     */
    public function getCoordinates(): Coordinates
    {
        return $this->coordinates;
    }

    /**
     * @return int
     */
    public function getLimit(): int
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
     * @param string     $name
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getData(string $name, $default = null)
    {
        if (!array_key_exists($name, $this->data)) {
            return $default;
        }

        return $this->data[$name];
    }

    /**
     * @return array
     */
    public function getAllData(): array
    {
        return $this->data;
    }

    /**
     * String for logging. This is also a unique key for the query.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('ReverseQuery: %s', json_encode([
            'lat' => $this->getCoordinates()->getLatitude(),
            'lng' => $this->getCoordinates()->getLongitude(),
            'locale' => $this->getLocale(),
            'limit' => $this->getLimit(),
            'data' => $this->getAllData(),
        ]));
    }
}
