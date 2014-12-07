<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder;

use Symfony\Component\Stopwatch\Stopwatch;

/**
 * This Geocoder allows you to profile your API/Database calls.
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class TimedGeocoder implements Geocoder
{
    private $delegate;

    private $stopwatch;

    public function __construct(Geocoder $delegate, Stopwatch $stopwatch)
    {
        $this->delegate  = $delegate;
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritDoc}
     */
    public function geocode($value)
    {
        $this->stopwatch->start('geocode', 'geocoder');

        try {
            $result = $this->delegate->geocode($value);
        } catch (\Exception $e) {
            $this->stopwatch->stop('geocode');

            throw $e;
        }

        $this->stopwatch->stop('geocode');

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function reverse($latitude, $longitude)
    {
        $this->stopwatch->start('reverse', 'geocoder');

        try {
            $result = $this->delegate->reverse($latitude, $longitude);
        } catch (\Exception $e) {
            $this->stopwatch->stop('reverse');

            throw $e;
        }

        $this->stopwatch->stop('reverse');

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getLimit()
    {
        return $this->delegate->getLimit();
    }

    /**
     * {@inheritDoc}
     */
    public function limit($limit)
    {
        return $this->delegate->limit($limit);
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->delegate, $method], $args);
    }
}
