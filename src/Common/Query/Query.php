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
    public function withLocale(string $locale): Query;

    public function withLimit(int $limit): Query;

    public function withData(string $name, mixed $value): Query;

    public function getLocale(): ?string;

    public function getLimit(): int;

    public function getData(string $name, mixed $default = null): mixed;

    /**
     * @return array<string, mixed>
     */
    public function getAllData(): array;

    public function __toString(): string;
}
