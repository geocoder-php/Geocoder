<?php

namespace Geocoder\Provider\CacheStrategy;

interface StratStrategyegy
{
    function invoke($key, callable $function);
}
