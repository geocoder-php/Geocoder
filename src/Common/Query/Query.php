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
     * @param mixed  $value
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
     * @param string     $name
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
    public function __toString();
}
