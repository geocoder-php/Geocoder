<?php

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Geocoder;

use Geocoder\Model\Query\GeocodeQuery;
use Geocoder\Model\Query\ReverseQuery;
use Geocoder\Provider\Provider;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * This Geocoder allows you to profile your API/Database calls.
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class TimedGeocoder implements Geocoder
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

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query)
    {
        $this->stopwatch->start('geocode', 'geocoder');

        try {
            $result = $this->delegate->geocodeQuery($query);
        } catch (\Exception $e) {
            $this->stopwatch->stop('geocode');

            throw $e;
        }

        $this->stopwatch->stop('geocode');

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query)
    {
        $this->stopwatch->start('reverse', 'geocoder');

        try {
            $result = $this->delegate->reverseQuery($query);
        } catch (\Exception $e) {
            $this->stopwatch->stop('reverse');

            throw $e;
        }

        $this->stopwatch->stop('reverse');

        return $result;
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->delegate, $method], $args);
    }

    public function getName()
    {
        return 'TimedGeocoder';
    }
}
