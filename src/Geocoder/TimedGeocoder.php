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
        $this->delegate = $delegate;
        $this->stopwatch = $stopwatch;
    }

    public function geocode($value)
    {
       $this->stopwatch->start('geocode', 'geocoder');
       $result = $this->delegate->geocode($value);
       $this->stopwatch->stop('geocode');

       return $result;
    }

    public function reverse($latitude, $longitude)
    {
       $this->stopwatch->start('reverse', 'geocoder');
       $result = $this->delegate->reverse($latitude, $longitude);
       $this->stopwatch->stop('reverse');

       return $result;
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->delegate, $method], $args);
    }
}
