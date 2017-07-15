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
     * @return Query
     */
    public function withLocale(string $locale);

    /**
     * @param int $limit
     *
     * @return Query
     */
    public function withLimit(int $limit);

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return Query
     */
    public function withData(string $name, $value);

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
