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

use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * This Geocoder allows you to profile your API/Database calls.
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
final class TimedGeocoder implements Geocoder
{
    use GeocoderTrait;

    /**
     * @var Provider
     */
    private $delegate;

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    public function __construct(Provider $delegate, Stopwatch $stopwatch)
    {
        $this->delegate = $delegate;
        $this->stopwatch = $stopwatch;
    }

    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $this->stopwatch->start('geocode', 'geocoder');

        try {
            $result = $this->delegate->geocodeQuery($query);
        } catch (\Throwable $e) {
            $this->stopwatch->stop('geocode');

            throw $e;
        }

        $this->stopwatch->stop('geocode');

        return $result;
    }

    public function reverseQuery(ReverseQuery $query): Collection
    {
        $this->stopwatch->start('reverse', 'geocoder');

        try {
            $result = $this->delegate->reverseQuery($query);
        } catch (\Throwable $e) {
            $this->stopwatch->stop('reverse');

            throw $e;
        }

        $this->stopwatch->stop('reverse');

        return $result;
    }

    public function __call(string $method, array $args): mixed
    {
        return call_user_func_array([$this->delegate, $method], $args);
    }

    public function getName(): string
    {
        return 'timed_geocoder';
    }
}
