<?php

declare(strict_types=1);

namespace Geocoder\Query;

use Geocoder\Model\Bounds;


/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface Query
{
    /**
     * @param string $locale
     *
     * @return GeocodeQuery
     */
    public function withLocale(string $locale): GeocodeQuery;

    /**
     * @param int $limit
     *
     * @return GeocodeQuery
     */
    public function withLimit(int $limit): GeocodeQuery;

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return GeocodeQuery
     */
    public function withData(string $name, $value): GeocodeQuery;

    /**
     * @return string|null
     */
    public function getLocale();

    /**
     * @return int
     */
    public function getLimit(): int;

    /**
     * @param string $name
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getData(string $name, $default = null);

    /**
     * @return array
     */
    public function getAllData(): array;

    /**
     * @return string
     */
    public function __toString(): string;
}
