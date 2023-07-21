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

use Geocoder\Exception\InvalidArgument;
use Geocoder\Geocoder;
use Geocoder\Model\Bounds;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class GeocodeQuery implements Query
{
    /**
     * The address or text that should be geocoded.
     *
     * @var string
     */
    private $text;

    /**
     * @var Bounds|null
     */
    private $bounds;

    /**
     * @var string|null
     */
    private $locale;

    /**
     * @var int
     */
    private $limit = Geocoder::DEFAULT_RESULT_LIMIT;

    /**
     * @var array<string, mixed>
     */
    private $data = [];

    private function __construct(string $text)
    {
        if ('' === $text) {
            throw new InvalidArgument('Geocode query cannot be empty');
        }

        $this->text = $text;
    }

    public static function create(string $text): self
    {
        return new self($text);
    }

    public function withText(string $text): self
    {
        $new = clone $this;
        $new->text = $text;

        return $new;
    }

    public function withBounds(Bounds $bounds): self
    {
        $new = clone $this;
        $new->bounds = $bounds;

        return $new;
    }

    public function withLocale(string $locale): self
    {
        $new = clone $this;
        $new->locale = $locale;

        return $new;
    }

    public function withLimit(int $limit): self
    {
        $new = clone $this;
        $new->limit = $limit;

        return $new;
    }

    public function withData(string $name, mixed $value): self
    {
        $new = clone $this;
        $new->data[$name] = $value;

        return $new;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getBounds(): ?Bounds
    {
        return $this->bounds;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getData(string $name, mixed $default = null): mixed
    {
        if (!array_key_exists($name, $this->data)) {
            return $default;
        }

        return $this->data[$name];
    }

    /**
     * @return array<string, mixed>
     */
    public function getAllData(): array
    {
        return $this->data;
    }

    /**
     * String for logging. This is also a unique key for the query.
     */
    public function __toString(): string
    {
        return sprintf('GeocodeQuery: %s', json_encode([
            'text' => $this->getText(),
            'bounds' => $this->getBounds() ? $this->getBounds()->toArray() : 'null',
            'locale' => $this->getLocale(),
            'limit' => $this->getLimit(),
            'data' => $this->getAllData(),
        ]));
    }
}
