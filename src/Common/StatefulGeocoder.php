<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder;

use Geocoder\Model\Bounds;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class StatefulGeocoder implements Geocoder
{
    /**
     * @var string|null
     */
    private $locale;

    /**
     * @var Bounds
     */
    private $bounds;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var Provider
     */
    private $provider;

    public function __construct(Provider $provider, ?string $locale = null)
    {
        $this->provider = $provider;
        $this->locale = $locale;
        $this->limit = Geocoder::DEFAULT_RESULT_LIMIT;
    }

    public function geocode(string $value): Collection
    {
        $query = GeocodeQuery::create($value)
            ->withLimit($this->limit);

        if (null !== $this->locale && '' !== $this->locale) {
            $query = $query->withLocale($this->locale);
        }

        if (null !== $this->bounds) {
            $query = $query->withBounds($this->bounds);
        }

        return $this->provider->geocodeQuery($query);
    }

    public function reverse(float $latitude, float $longitude): Collection
    {
        $query = ReverseQuery::fromCoordinates($latitude, $longitude)
            ->withLimit($this->limit);

        if (null !== $this->locale && '' !== $this->locale) {
            $query = $query->withLocale($this->locale);
        }

        return $this->provider->reverseQuery($query);
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $locale = $query->getLocale();
        if ((null === $locale || '' === $locale) && null !== $this->locale) {
            $query = $query->withLocale($this->locale);
        }

        $bounds = $query->getBounds();
        if (empty($bounds) && null !== $this->bounds) {
            $query = $query->withBounds($this->bounds);
        }

        return $this->provider->geocodeQuery($query);
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        $locale = $query->getLocale();
        if ((null === $locale || '' === $locale) && null !== $this->locale) {
            $query = $query->withLocale($this->locale);
        }

        return $this->provider->reverseQuery($query);
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function setBounds(Bounds $bounds): self
    {
        $this->bounds = $bounds;

        return $this;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function getName(): string
    {
        return 'stateful_geocoder';
    }
}
